<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;    

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
    $data = [];
    if (\Auth::check()) { // 認証済みの場合
        // 認証済みユーザーを取得
        $user = \Auth::user();
        // ユーザーの投稿の一覧を作成日時の降順で取得
        $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
        $data = [
            'user' => $user,
            'tasks' => $tasks,
        ];

        // ビューでそれらのデータを使用
        return view('tasks.index', $data);
        }
        // 認証していない場合はログインページにリダイレクト
        return redirect()->route('login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task;
         
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|max:10',   
            'content' => 'required|max:255',
        ]);
    
        $user = auth()->user(); // 現在のログインユーザーを取得
        $task = new Task;
        $task->content = $request->content;
        $task->status = $request->status ?? 'default';
        $task->user_id = $user->id; // ユーザーのIDを設定
        $task->save();
        
        return redirect('/');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::findOrFail($id);
        
        if ($task->user_id !== \Auth::id()) {
            return redirect('/'); // トップページにリダイレクト
        }

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = Task::findOrFail($id);
        
        if ($task->user_id !== \Auth::id()) {
        return redirect('/');
    }

       
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== \Auth::id()) {
            return redirect('/');
        }
        
        $request->validate([
            'status' => 'required|max:10',   
            'content' => 'required|max:255',
        ]);
    
       
    
        $task->status = $request->status;  
        $task->content = $request->content;
        $task->save();
    
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $task = Task::findOrFail($id);

        if (\Auth::id() === $task->user_id) {
            $task->delete();
            return redirect('/')
                ->with('success', 'Delete Successful');
        }

        return redirect('/');
    }
}