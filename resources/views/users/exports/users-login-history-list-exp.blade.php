<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        @php
            use Carbon\Carbon;
        @endphp
    <table>
        <thead>
            <tr>
                <th style="font-weight: bold;">Username</th>
                <th style="font-weight: bold;">Number of logins</th>
                <th style="font-weight: bold;">First login date</th>
                <th style="font-weight: bold;">Last login date</th>
            </tr>
        </thead>
        <tbody>
            
            @foreach($query as $item)
                <tr>
                    <td>
                        {{ $item->user_id != null ? ($item->user_detail->name != null ? $item->user_detail->name : 'N.A') : 'N.A' }}
                    </td>
                    <td>
                        {{ $item->number_of_login != null ? $item->number_of_login : 'N.A' }}
                    </td>
                    <td>
                        {{ $item->first_login != null ? Carbon::parse($item->first_login)->format('d/m/Y h:i A') : "N.A" }}
                    </td>
                    <td>
                        {{ $item->last_login != null ? Carbon::parse($item->last_login)->format('d/m/Y h:i A') : "N.A" }}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
    </body>
</html>