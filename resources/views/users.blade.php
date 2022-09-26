{{-- @include('header')

<h1>this is the user page whats up </h1>
@if(in_array("akorede", $name))
<h1> Yes {{$name[2]}} is present</h1>
@else
<h1 id="tag">he is not present fa</h1>
@endif

@for($i = 0; $i<=10; $i++)
<span>{{$i}}</span>
@endfor

@foreach ($name as $key => $user)
    <p>{{$user}}</p>
@endforeach

@csrf
<script>
    let data = @json($name);
    let item = 'milk';
    document.getElementById('tag').textContent += item;
</script> --}}
<style>
    .formie{
        margin:50px;
        padding: 10px;
    }
    .inpt{
        width:200px;
        height: 30px;
        background-color: azure;
        font-size: medium;
        border-radius: 4px;
        color: red;
    }
    .btn{
        color:white;
        background-color: mediumblue;
        width:200px;
        height: 30px;
        border: none;
        border-radius: 6px;
        font-size: large;
    }

</style>

{{-- @if($errors->any())
@foreach ($errors->all() as $err)
    <li>{{$err}}</li>
@endforeach
@endif --}}
<div class="formie">
   <form action="user" method="post">
    @csrf
        <input class = "inpt" type="text" name="first_name" placeholder="enter your first name"> <br> <br>
        @error('first_name')
        <span>{{$message}}</span> <br> <br>
        @enderror
        <input class = "inpt" type="text" name="last_name" placeholder="enter your last name"> <br> <br>
        @error('last_name')
        <span>{{$message}}</span> <br> <br>
        @enderror
        <input class = "inpt" type="email" name="email" placeholder="email"> <br> <br>
        @error('email')
        <span>{{$message}}</span> <br> <br>
        @enderror
        <input class = "inpt" type="password" name="password" placeholder="password"> <br> <br>
        @error('password')
        <span>{{$message}}</span> <br> <br>
        @enderror
        <input class = "btn"  type="submit" name="submit" value="submit">
    </form> 
</div>
