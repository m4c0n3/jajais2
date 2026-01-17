<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install</title>
</head>
<body>
    <h1>System Install</h1>

    <form method="POST" action="{{ route('install.submit') }}">
        @csrf

        <div>
            <label for="mode">Mode</label>
            <select id="mode" name="mode">
                <option value="client" @selected(old('mode', $mode) === 'client')>Client</option>
                <option value="control-plane" @selected(old('mode', $mode) === 'control-plane')>Control plane</option>
            </select>
        </div>

        <div>
            <label for="admin_name">Admin name</label>
            <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name', 'Admin') }}" required>
        </div>

        <div>
            <label for="admin_email">Admin email</label>
            <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" required>
        </div>

        <div>
            <label for="admin_password">Admin password</label>
            <input id="admin_password" name="admin_password" type="password" required>
        </div>

        <button type="submit">Install</button>
    </form>
</body>
</html>
