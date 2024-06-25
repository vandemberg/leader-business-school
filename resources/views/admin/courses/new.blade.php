@extends('layouts.admin')

@section('content')
<form action="POST" action="/admin/courses">
    @csrf
    <div class="group">
        <label for="name" class="input">Nome do curso</label>
        <input type="text" name="name" />
    </div>

    <div>
        <label for="description">Descrição do curso</label>
        <textarea name="description"></textarea>
    </div>

    <div>
        <label for=""></label>
        <input type="file">
    </div>
</form>
@endsection
