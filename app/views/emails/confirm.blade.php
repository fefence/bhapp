<html>
<head></head>
<body>
<p>
    <span style="font-size:18px"><a href="{{$confirm_link}}" target="_blank">[confirm]</a></span><br><br>
    {{$home}} - {{$away}}<br>
    </p>
    <table border="1px">
        <tbody>
        <tr>
            @foreach($res as $r)
            @if($r->resultShort == 'D')
                <td style="background-color:#ffbf00">D</td>
            @else
                <td style="background-color:#ddd">{{$r->resultShort}}</td>
            @endif
            @endforeach
        </tr>
        </tbody>
</table>
{{$body}}
<a href="{{$link_to_group}}" target="_blank">[view on bhapp.eu]</a>
</body>
</html>