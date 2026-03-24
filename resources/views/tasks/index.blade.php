@extends('adminlte::page')

@section('title', 'Tasks')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Add Task
        </a>
    </div>
@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Project Filter --}}
    <div class="card card-outline card-secondary">
        <div class="card-body">
            <form method="GET" action="{{ route('tasks.index') }}" class="form-inline">
                <label for="project_id" class="mr-2 font-weight-bold">Filter by Project:</label>
                <select name="project_id" id="project_id" class="form-control mr-2">
                    <option value="">All Projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary mr-2">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i>Clear
                </a>
            </form>
        </div>
    </div>

    {{-- Tasks List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks mr-1"></i> Task List
                <small class="text-muted ml-2"><i class="fas fa-grip-vertical"></i> Drag rows to reorder</small>
            </h3>
        </div>
        <div class="card-body p-0">
            @if ($tasks->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No tasks found.
                        <a href="{{ route('tasks.create') }}">Create your first task</a>
                    </p>
                </div>
            @else
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40"></th>
                            <th width="80">Priority</th>
                            <th>Task Name</th>
                            <th>Project</th>
                            <th>Created</th>
                            <th width="130">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-tasks">
                        @foreach ($tasks as $task)
                            <tr data-id="{{ $task->id }}" style="cursor: grab;">
                                <td class="text-center text-muted">
                                    <i class="fas fa-grip-vertical"></i>
                                </td>
                                <td>
                                    <span class="badge badge-primary priority-badge">
                                        #{{ $task->priority }}
                                    </span>
                                </td>
                                <td>{{ $task->name }}</td>
                                <td>
                                    @if ($task->project)
                                        <a href="{{ route('tasks.index', ['project_id' => $task->project_id]) }}"
                                            class="badge badge-info">
                                            {{ $task->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $task->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete this task?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const tbody = document.getElementById('sortable-tasks');

        if (tbody) {
            Sortable.create(tbody, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function() {
                    const rows = tbody.querySelectorAll('tr[data-id]');
                    const taskIds = Array.from(rows).map(r => parseInt(r.dataset.id));

                    // Update priority badges immediately (optimistic UI)
                    rows.forEach((row, index) => {
                        const badge = row.querySelector('.priority-badge');
                        if (badge) badge.textContent = '#' + (index + 1);
                    });

                    fetch('{{ route('tasks.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                task_ids: taskIds
                            }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Failed to save order. Please refresh the page.');
                            }
                        })
                        .catch(() => {
                            alert('Network error while saving order. Please refresh.');
                        });
                }
            });
        }
    </script>
@stop
