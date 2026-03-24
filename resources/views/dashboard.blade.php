@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $tasks->count() }}</h3>
                    <p>Total Tasks</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
                <a href="{{ route('tasks.index') }}" class="small-box-footer">
                    View Tasks <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $projects->count() }}</h3>
                    <p>Total Projects</p>
                </div>
                <div class="icon"><i class="fas fa-project-diagram"></i></div>
                <a href="{{ route('projects.index') }}" class="small-box-footer">
                    View Projects <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
@stop
