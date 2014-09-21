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