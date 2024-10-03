<!DOCTYPE html>
<html>
<head>
    <title>Task Starting Soon</title>
</head>
<body>
    <h1>まもなく {{ $task->name }} の時間です！！</h1>
    <p>Start Time: {{ $task->start_time }}</p>
    <p>Description: {{ $task->description }}</p>
</body>
</html>