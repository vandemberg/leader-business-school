<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
  <title>Admin LeaderPlus</title>
</head>

<body>
  <nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
      <a class="navbar-item" href="admin/">
        <img src="https://bulma.io/images/bulma-logo.png" width="112" height="28">
      </a>
    </div>
    <div class="navbar-menu">
      <div class="navbar-start">
        <a class="navbar-item"></a>Home</a>
        <a class="navbar-item">Users</a>
        <a class="navbar-item">Roles</a>
        <a class="navbar-item">Permissions</a>
      </div>
    </div>
  </nav>

  <div class="container">
    @yield('content')
  </div>
</body>

</html>
