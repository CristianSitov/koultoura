<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Subscribers</title>

    <style>
        body, table, th, td {font-family: DejaVu Sans;}
        table {
            border: 0.3pt solid #cccccc;
            border-collapse: collapse;
        }

        td, th {
            text-align: left;
            border: 0.3pt solid #cccccc;
            font-size: 8pt;
            padding: 2pt;
            vertical-align: top;
        }
        .name {
            font-size: 10pt;
        }
    </style>
</head>
<body>
<div style="width: 100%">
    <table style="width: 100%">
        <thead>
        <tr class="table-danger">
            <th style="width: 10%">#</th>
            <th style="width: 30%">Name</th>
            <th style="width: 60%">Email/Phone/Org./Job</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        @foreach($subscribers as $l => $subscriber)
            <tr>
                <th scope="row">{{ $l }} / {{ $subscriber['id'] }}<br />
                    <small>{{ implode(',', $subscriber['profile']['event_details']->days) }}</small></th>
                <td class="name">{{ $subscriber['last_name'] }} {{ $subscriber['first_name'] }}</td>
                <td><small>{{ $subscriber['email'] }}<br />
                    {{ $subscriber['profile']['phone'] }}<br />
                    {{ $subscriber['profile']['organization'] }}<br />
                    {{ $subscriber['profile']['job'] }}</small></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
