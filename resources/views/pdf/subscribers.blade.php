<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Subscribers</title>

    <style>
        table {
            border: 0.3pt solid #cccccc;
            border-collapse: collapse;
        }

        td, th {
            border: 0.3pt solid #cccccc;
            font-size: 10pt;
            padding: 4pt;
            vertical-align: top;
        }
    </style>
</head>
<body>
<div style="width: 100%">
    <table>
        <thead>
        <tr class="table-danger">
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Phone</th>
            <th scope="col">Org./Job</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        @foreach($subscribers as $subscriber)
            <tr>
                <th scope="row">{{ $subscriber['id'] }}</th>
                <td>{{ $subscriber['name'] }}</td>
                <td>{{ $subscriber['email'] }}</td>
                <td>{{ $subscriber['profile']['phone'] }}</td>
                <td>{{ $subscriber['profile']['organization'] }}<br><small>{{ $subscriber['profile']['job'] }}</small></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
