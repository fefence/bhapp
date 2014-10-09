<html>
<head>
    <script type="text/css">
        th, td {
            padding: 5px;
            vertical-align: top;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            margin: 0 auto;
        }
        .draw {
            background-color:#EBCC6E;
        }
        .nodraw {
            background-color:#EBE1C5;
        }
    </script>
</head>
<body>
<p>
    <span style="font-size:18px"><a href="{{$confirm_link}}" target="_blank">[confirm]</a></span><br><br>
    {{$home}} - {{$away}} ({{date('d M', strtotime($date))}}, {{substr($time, 0, strlen($time)-3)}})<br>
    </p>
<table>
    @foreach($body as $type => $b)
        <tr>
            <td style="text-align: right;">{{$b['confirmed']}}</td>
            <td style="text-align: right;">[{{$type}}]</td>
            <td style="text-align: right;">[{{$b['length']}}]</td>
            <td style="text-align: right;">[BSF: {{$b['bsf']}}€]</td>
            <td style="font-size: 110%; font-weight: bold; text-align: right; color: darkred;">{{$b['bet']}}€</td>
            <td style="text-align: right;">@</td>
            <td style="font-size: 110%; font-weight: bold; text-align: right; color: darkred;">{{$b['odds']}}</td>
            <td style="text-align: right;">for</td>
            <td style="text-align: right;">{{$b['profit']}}€</td>
        </tr>
    @endforeach
</table>
<br>
<table style="border-collapse: collapse; border-spacing: 0px; margin: 0;">
    <tbody>
    <tr>
        @foreach($res as $r)
        @if($r->resultShort == 'D')
        <td style="padding: 8px;vertical-align: top; background-color: #FDEC6F; color: #594433; border: 1px solid #BCB7AB;">D</td>
        @else
        <td style="padding: 8px;vertical-align: top; background-color: #F4F4F4; color: #594433; border: 1px solid #BCB7AB;">{{$r->resultShort}}</td>
        @endif
        @endforeach
    </tr>
    </tbody>
</table>
<br>
<a href="{{$link_to_group}}" target="_blank">[view on bhapp.eu]</a>
</body>
</html>