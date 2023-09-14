@extends('channels.layout')
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>General configuration channels</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('channels.create') }}"> Create New Channel</a>
            </div>
        </div>
    </div>
   
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
   
    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Create</th>
            <th>Update</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($channels as $channel)
        <tr>
            {{-- <td>{{ ++$i }}</td> --}}
            <td>{{ $channel->name }}</td>
            <td>{{ $channel->description }}</td>
            <td>{{ $channel->created_at }}</td>
            <td>{{ $channel->updated_at }}</td>
            <td>
                <form action="{{ route('channels.destroy',$channel->id) }}" method="POST">
   
                    <a class="btn btn-info" href="{{ route('channels.show',$channel->id) }}">Show</a>
    
                    <a class="btn btn-primary" href="{{ route('channels.edit',$channel->id) }}">Edit</a>
   
                    @csrf
                    @method('DELETE')
      
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
  
    {!! $channels->links() !!}
      
@endsection