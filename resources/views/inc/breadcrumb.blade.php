<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{route("home")}}">Home</a></li>
    @php
    $segments = '';
    @endphp
    @for($i = 2; $i <= count(Request::segments()); $i++)
        @if($i == count(Request::segments()))
            <li class="breadcrumb-item active">{{ucfirst(Request::segment($i))}}</li>
        @else
            <li class="breadcrumb-item">
                <a href="">
                {{ucfirst(Request::segment($i))}}
            </a>
            </li>
        @endif
       
    @endfor
    

</ol>