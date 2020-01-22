<!DOCTYPE html>
<html>
<head>
    <title>Role</title>
    <link rel="stylesheet" href="{{ URL::to('css/bootstrap.min.css') }}">
</head>
<body>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 align="center">Daftar Role</h3>
      </div>
      <div class="panel-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Name</th>
              <th>Display name</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            @foreach($roles as $role)
              <tr>
                <td>{{++$no}}</td>
                <td>{{$role->name}}</td>
                <td>{{$role->display_name}}</td>
                <td>{{$role->description}}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
</body>
</html>
