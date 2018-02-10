<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Channel;
use App\Performer;
use App\Song;
use App\Play;
use Response;

/**
 * @resource ApiController
 *
 * Manages API calls related to channel management
 */
class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Add a new channel to the database.
     *
     * @return Response {result: {...}, code: 0, errors: [ERROR_DESC_1, ERROR_DESC_2,...]}
     */
    public function addChannel(Request $request)
    {
        $response = [];
         
        //TODO: add simple auth?
        //if (Auth::attempt(['username' => $request->user, 'password' => $request->pass ])) {

            // Checks requested variable have been sent
            if($request->name == NULL){            
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 1;
                $response["errors"] = ["Missed param: name"];
            }else{
                $channel = DB::table('channels')->where('name', $request->name)->first();

                if($channel == null){ //Dismiss operation if it's already in the db
                    $channel = new Channel;
                    $channel->name = $request->name;

                    if ($channel->save()){
                        $response["result"] = $channel;
                        $response["code"] = 0;
                    }else{
                        $response["result"] = json_encode(new \stdClass);
                        $response["code"] = 2;
                        $response["errors"] = ["Error: save failed"];
                    }
                }
                /*else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 3;
                    $response["errors"] = ["channel already exists"];
                }*/
            }
        /*
        }else{
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 10;
            $response["errors"] = ["Login incorrect"];
        }
        */

        return Response::json($response);
    }

    /**
     * Stores a new performer.
     *
     * @return Response {result: {...}, code: 0, errors: [ERROR_DESC_1, ERROR_DESC_2,...]}
     */
    public function addPerformer(Request $request)
    {
        $response = [];

        // Checks requested variable have been sent
        if($request->name == NULL){            
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: name"];
        }else{
            $performer = DB::table('performers')->where('name', $request->name)->first();

            if($performer == null){ //Dismiss storing if it's already in the db
                $performer = new Performer;
                $performer->name = $request->name;

                if ($performer->save()){
                    $response["result"] = $performer;
                    $response["code"] = 0;
                }else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 2;
                    $response["errors"] = ["Error: save failed"];
                }
            }
            /*else{
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 3;
                $response["errors"] = ["Performer already exists"];
            }*/
        }

        return Response::json($response);
    }

    /**
     * Stores a new song in the database.
     *
     * @return Response {result: {...}, code: 0, errors: [ERROR_DESC_1, ERROR_DESC_2,...]}
     */
    public function addSong(Request $request)
    {
        $response = [];

        // Checks requested variables have been sent
        if($request->title == NULL || $request->performer == NULL){
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: title or performer"];
        }else{
            $song = null;
            $performer = DB::table('performers')->where('name', $request->performer)->first();

            if($performer == null){ // Created the performer if it doesn't exist
                $performer = new Performer;
                $performer->name = $request->performer;
                $performer->save();
            }else{ //Check whether the song already exists
                $song = DB::table('songs')
                ->join('performers', 'performers.id', '=', 'songs.performer_id')
                ->select('songs.id','songs.title','performers.name')
                ->where('songs.title', $request->title)
                ->where('performers.name', $request->performer)
                ->first();
            }

            if($song == null){ //Dismiss storing if it's already in the db
                $song = new Song;
                $song->title = $request->title;
                $song->performer_id = $performer->id;

                if ($song->save()){
                    $response["result"] = $song;
                    $response["code"] = 0;
                }else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 2;
                    $response["errors"] = ["Error: save failed"];
                }
            }
        }

        return Response::json($response);
    }

    /**
     * Stores a new play in the database.
     *
     * @return Response {result: {...}, code: 0, errors: [ERROR_DESC_1, ERROR_DESC_2,...]}
     */
    public function addPlay(Request $request)
    {
        $response = [];

        // Checks requested variables have been sent
        if($request->title == NULL || $request->performer == NULL || $request->start == NULL || $request->end == NULL || $request->channel == NULL){            
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: title, performer, start, end or channel missed"];
        }else{
            if($request->end > $request->start){
                // Checks that elements exist
                $song = DB::table('songs')
                ->join('performers', 'performers.id', '=', 'songs.performer_id')
                ->select('songs.id','songs.title','performers.name') 
                ->where('songs.title', $request->title)
                ->where('performers.name', $request->performer)
                ->first();

                $channel = DB::table('channels')->where('name', $request->channel)->first();

                if($channel != null && $song != null){
                    $play = DB::table('plays')
                    ->where('plays.song_id', $song->id)
                    ->where('plays.channel_id', $channel->id)
                    ->where('plays.start', $request->start)
                    ->where('plays.end', $request->end)
                    ->first();

                    if($play == null){
                        $play = new Play;
                        $play->channel_id = $channel->id;
                        $play->song_id = $song->id;
                        $play->start = $request->start;
                        $play->end = $request->end;

                        if ($play->save()){
                            $response["result"] = $play;
                            $response["code"] = 0;
                        }else{
                            $response["result"] = json_encode(new \stdClass);
                            $response["code"] = 2;
                            $response["errors"] = ["Error: save failed"];
                        }
                    }
                }else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 6;
                    $response["errors"] = ["Channel or song missed"];
                }
            }else{
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 7;
                $response["errors"] = ["Incorrect datetime: end <= start"];
            }
        }

        return Response::json($response);
    }

    /**
     * Returns all the plays that occur between two datetimes for a song.
     *
     * @return Response [{channel: 'channel', start: '2014-01-10T01:00:00', end: '2014-01-10T01:03:00'],...], code: 0}
     */
    public function getSongPlays(Request $request)
    {
        $response = [];

        // Checks requested variables have been sent
        if($request->title == NULL || $request->performer == NULL || $request->start == NULL || $request->end == NULL){            
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: title, performer, start or end missed"];
        }else{
            if($request->end > $request->start){
                // Checks that song exists
                $song = DB::table('songs')
                ->join('performers', 'performers.id', '=', 'songs.performer_id')
                ->select('songs.id','songs.title','performers.name') 
                ->where('songs.title', $request->title)
                ->where('performers.name', $request->performer)
                ->first();

                if($song != null){
                    $plays = DB::table('plays')
                    ->join('channels', 'channels.id', '=', 'plays.channel_id')
                    ->select('channels.name as channel','plays.start','plays.end')
                    ->where('plays.song_id', $song->id)
                    ->where('plays.start','>=', $request->start)
                    ->where('plays.end','<=', $request->end)
                    ->get();

                    $response["result"] = [];
                    $response["code"] = 0;

                    foreach ($plays as $key => $play) {
                        array_push($response["result"], $play);
                    }
                }else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 6;
                    $response["errors"] = ["Song missed"];
                }
            }else{
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 7;
                $response["errors"] = ["Incorrect datetime: end <= start"];
            }
        }

        return Response::json($response);
    }

    /**
     * Returns all the plays of a station between two datetimes.
     *
     * @return Response [{performer: 'performer_name', title: 'title', start: '2014-10-21T00:00:00',
     *                      end: '2014-10-21T00:03:00'},...],
     */ 
    public function getChannelPlays(Request $request)
    {
        $response = [];

        // Checks requested variables have been sent
        if($request->channel == NULL || $request->start == NULL || $request->end == NULL){            
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: start, end or channel missed"];
        }else{
            if($request->end > $request->start){
                // Checks that song exists
                $channel = DB::table('channels')
                ->select('channels.id') 
                ->where('channels.name',$request->channel)
                ->first();

                if($channel != null){
                    $plays = DB::table('plays')
                    ->join('songs', 'songs.id', '=', 'plays.song_id')
                    ->join('performers', 'performers.id', '=', 'songs.performer_id')
                    ->select('performers.name as performer','songs.title','plays.start','plays.end')
                    ->where('plays.channel_id', $channel->id)
                    ->where('plays.start', '>=', $request->start)
                    ->where('plays.end', '<=', $request->end)
                    ->get();

                    $response["result"] = [];
                    $response["code"] = 0;

                    foreach ($plays as $key => $play) {
                        array_push($response["result"], $play);
                    }
                }else{
                    $response["result"] = json_encode(new \stdClass);
                    $response["code"] = 6;
                    $response["errors"] = ["Channel missed"];
                }
            }else{
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 7;
                $response["errors"] = ["Incorrect datetime: end <= start"];
            }
        }

        return Response::json($response);
    }

    /**
     * Returns a ranking of the most popular songs of a week.
     *
     * @return Response {result: [{performer: 'performer', title: 'title', plays: plays, previous_plays: previous_plays,
     *                  rank: rank, previous_rank: previous_rank], ...], code: 0}
     */
    public function getTop(Request $request)
    {
        $response = [];

        // Checks requested variables have been sent
        if($request->channels == NULL || $request->start == NULL || $request->limit == NULL){            
            $response["result"] = json_encode(new \stdClass);
            $response["code"] = 1;
            $response["errors"] = ["Missed param: start, end or channel missed"];
        }else{
            if($request->limit > 0){
                $channels = json_decode($request->channels);
                $response["code"] = 0;
                $response["result"] = $this->computeTopWeek($request->start, $channels, $request->limit);
            }else{
                $response["result"] = json_encode(new \stdClass);
                $response["code"] = 8;
                $response["errors"] = ["Limit ought to be a positive number"];
            }
        }

        return Response::json($response);
    }

    /**
     * Returns the  'n' (set by $limit) most played songs of a week from a given $date, played in the following $channels
     *
     * @return Plays [{performer: 'performer', title: 'title', plays: plays, previous_plays: previous_plays,
     *                  rank: rank, previous_rank: previous_rank], ...]
     */
    private function computeTopWeek($date, $channels, $limit){
        $time = strtotime($date);
        $next_week = date('Y-m-d H:i:s',strtotime("+7 day", $time) );
        $previous_week = date('Y-m-d\TH:i:s',strtotime("-7 day", $time) );

        //Compute the next week
        $plays = DB::table('plays')
        ->select(DB::raw('count(*) as plays'), 'performers.name as performer', 'songs.title')
        ->join('channels', 'channels.id', '=', 'plays.channel_id')
        ->join('songs', 'songs.id', '=', 'plays.song_id')
        ->join('performers', 'performers.id', '=', 'songs.performer_id')
        ->where('plays.start','>=', $date)
        ->where('plays.end','<=', $next_week)
        ->whereIn('channels.name', $channels)
        ->groupBy('songs.title','performers.name')
        ->orderBy('plays', 'desc')
        ->limit($limit)
        ->get();

        //Compute previous week for previous plays and rank
        $previous_plays = DB::table('plays')
        ->select(DB::raw('count(*) as plays'), 'performers.name as performer', 'songs.title')
        ->join('channels', 'channels.id', '=', 'plays.channel_id')
        ->join('songs', 'songs.id', '=', 'plays.song_id')
        ->join('performers', 'performers.id', '=', 'songs.performer_id')
        ->where('plays.start','>=', $previous_week)
        ->where('plays.end','<=', $date)
        ->whereIn('channels.name', $channels)
        ->groupBy('songs.title','performers.name')
        ->orderBy('plays', 'desc')
        ->get();
        
        //Merge previous plays 
        foreach ($plays as $i => $play) {
            $play->rank = $i;
            $play->previous_rank = NULL;
            $play->previous_plays = 0;

            foreach ($previous_plays as $j => $previous) {
                if($play->performer == $previous->performer && $play->title == $previous->title){
                    $play->previous_plays = $previous->plays;
                    $play->previous_rank = $j;
                }
            }
        }

        return $plays;
    }
}
