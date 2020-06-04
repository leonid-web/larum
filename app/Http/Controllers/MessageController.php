<?php

namespace App\Http\Controllers;


use App\Answer;
use App\Message;
use App\Notifications\AddAnswer;
use App\Notifications\RepliedToThread;
use App\Theme;
use App\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return Message::all();
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


    // Вывод сообщений для ajax
    public function ajaxMessages(Request $request, $id){
        $theme = Theme::find($id);
        $messages = $theme->messages()->paginate(5);
            return ($messages);
    }


    public function store(Request $request)
    {
        $users=User::all();
        $theme = Theme::find($request->theme_id);
        Message::create([
                'user_id' => auth()->user()->id
            ] + $request->all());

        $messages = Theme::find($request->theme_id)->messages()->paginate(5);
        foreach ($users as $user){
            if ($user->id==$theme->owner_id){
                $user->notify(new RepliedToThread($user));
            }
        }
        return view('messages', ['messages' =>$messages]);

    }

    public function show(Message $message)
    {
        return view('showMessage', ['message' => $message]);
    }

    //ответ на сообщение
    public function store_answer(Request $request, $id)
    {

        $users=User::all();
//        $id->addAnswer($request['body']);
        $messages = Message::find($id); //Почему то без этого не работает
        Answer::create(
            [
                'body' => $request['body'],
                'user_id' => auth()->user()->id,
                'messages_id' =>$messages->id,

            ]
        );
        foreach ($users as $user){
            if ($user->id==$messages->user_id){
                $user->notify(new AddAnswer($user));
            }
        }
        return redirect()->back();
    }

    public function update( $id, Request $request)
    {
        $messege = Message::find($id);
        $messege ->fill($request->all());
        $messege->save();
        return back();
    }

    public function destroy(Message $message)
    {
        return $message->delete();
    }



}
