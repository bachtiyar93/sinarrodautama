<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Requests\Api\v1\TaskRequest;
use App\Http\Resources\Api\v1\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TaskResource::collection(Task::with('assignee')->latest()->get());
    }

    public function store(TaskRequest $request): TaskResource
    {
        $task = Task::create($request->validated());
        return new TaskResource($task->load('assignee'));
    }

    public function show(Task $task): TaskResource
    {
        return new TaskResource($task->load('assignee'));
    }

    public function update(TaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());
        return new TaskResource($task->load('assignee'));
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
