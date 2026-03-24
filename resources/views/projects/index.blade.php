@extends('adminlte::page')

@section('title', 'Projects')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Add Project
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

    <div class="card">
        <div class="card-body p-0">
            @if ($projects->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-project-diagram fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No projects yet.
                        <a href="{{ route('projects.create') }}">Create your first project</a>
                    </p>
                </div>
            @else
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Tasks</th>
                            <th>Created</th>
                            <th width="130">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $index => $project)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $project->name }}</strong></td>
                                <td>{{ $project->description ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}"
                                        class="badge badge-info">
                                        {{ $project->tasks_count }} tasks
                                    </a>
                                </td>
                                <td>{{ $project->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this project? Tasks will be unlinked.');">
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
