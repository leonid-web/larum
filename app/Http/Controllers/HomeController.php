<?php

namespace App\Http\Controllers;

use App\Events;
use App\Message;
use App\Roles;
use App\Status;
use App\Theme;
use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        $this->middleware('auth');
//    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(User $user)
    {
        $events = Events::all();
        $events = $events->sortBy('date');
        $users = User::all();
        $statuses = Status::all();
        $roles = Roles::all();
        $themes = Theme::all();
        //      $events = DB::table('events')->get(); //Это второй способ обращения к базе данных. В этом случае добавляем: use Illuminate\Support\Facades\DB;
        return view('home', ['events' => $events, 'users' => $users, 'statuses' => $statuses, 'roles' => $roles, 'themes' => $themes]);
    }

    public function store(Request $request, $id)
    {
        $events = Events::find($id); //Почему то без этого не работает
        $events->themes()->create(
            array_merge(
                [
                    'owner_id' => auth()->user()->id,
                    'events_id' => $events->id,
                ],
                $request->all()
            )
        );
        return redirect()->back();
    }
// Вывод сообщений
    public function showMessages(Request $request, $id){
        $theme = Theme::find($id);;
        $messages = $theme->messages()->paginate(5);
        if ($request->ajax()) {
            return view('messages', compact('messages'));
        }
        return view('theme', ['theme' =>$theme, 'messages' => $messages]);
 }
}
