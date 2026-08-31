@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

    <div class="container-fluid">

        <h1 class="mb-4">
            Admin Dashboard
        </h1>

        <div class="row">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        Users
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        Students
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        Teachers
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        Courses
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection