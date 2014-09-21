
@if(array_get($data, 'team') == '')
    <a href="#" type="button" data-toggle="tooltip" data-placement="top" title="<strong>{{array_get($data, 'match')->homeGoals}}:{{array_get($data, 'match')->awayGoals}}</strong>&nbsp;({{array_get($data, 'match')->home}}&nbsp;-&nbsp;{{array_get($data, 'match')->away}})<br/>{{ date("d.m.Y",strtotime(array_get($data, 'match')->matchDate)) }}" class="btn hasTooltip
    @if(isset($game_type))
        @if(array_get($data, 'match')->resultShort == 'H' || array_get($data, 'match')->resultShort == 'A')
            btn-success btn-xs w25">{{array_get($data, 'match')->resultShort}}
        @elseif(array_get($data, 'match')->resultShort == 'D')
            @if(($game_type == '0-0' && array_get($data, 'match')->homeGoals == 0)|| ($game_type == '1-1' && array_get($data, 'match')->homeGoals == 1)|| ($game_type == '2-2' && array_get($data, 'match')->homeGoals == 2)))
            btn-warning btn-xs w26">{{array_get($data, 'match')->homeGoals}}-{{array_get($data, 'match')->awayGoals}}
            @elseif($game_type == '1X2')
            btn-warning btn-xs w25">{{array_get($data, 'match')->resultShort}}
            @else
            btn-success btn-xs w25">{{array_get($data, 'match')->resultShort}}
            @endif
        @elseif(array_get($data, 'match')->resultShort == '-' || array_get($data, 'match')->state == 'Canceled')
            btn-info btn-xs w25">{{array_get($data, 'match')->resultShort}}
        @endif
    @else
        @if(array_get($data, 'match')->resultShort == 'H' || array_get($data, 'match')->resultShort == 'A')
            {{"btn-success"}}
        @elseif(array_get($data, 'match')->resultShort == 'D')
            {{"btn-warning"}}
        @elseif(array_get($data, 'match')->resultShort == '-' || array_get($data, 'match')->state == 'Canceled')
            {{"btn-info"}}
        @endif
        btn-xs w25">{{array_get($data, 'match')->resultShort}}
    @endif
    </a>
@else
<a href="#" type="button" data-toggle="tooltip" data-placement="top" title="<strong>{{array_get($data, 'match')->homeGoals}}:{{array_get($data, 'match')->awayGoals}}</strong>&nbsp;({{array_get($data, 'match')->home}}&nbsp;-&nbsp;{{array_get($data, 'match')->away}})<br/>{{ date("d.m.Y",strtotime(array_get($data, 'match')->matchDate)) }}"
    @if(array_get($data, 'match')->resultShort == 'D')
    {{'class="btn btn-warning btn-xs w25 hasTooltip">D'}}
    @elseif((array_get($data, 'match')->resultShort == 'H' && array_get($data, 'match')->home == array_get($data, 'team')) || (array_get($data, 'match')->resultShort == 'A' && array_get($data, 'match')->away == array_get($data, 'team')))
    {{'class="btn btn-success btn-xs w25 hasTooltip">W'}}
    @elseif((array_get($data, 'match')->resultShort == 'A' && array_get($data, 'match')->home == array_get($data, 'team')) || (array_get($data, 'match')->resultShort == 'H' && array_get($data, 'match')->away == array_get($data, 'team')))
    {{'class="btn btn-danger btn-xs w25 hasTooltip">L'}}
    @else
    {{'class="btn btn-info btn-xs w25 hasTooltip">?'}}
    @endif
</a>
@endif