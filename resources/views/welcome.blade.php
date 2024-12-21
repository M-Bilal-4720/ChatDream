<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

</head>
<body>

<h2 id="trade">Trade Data</h2>
<script src="{{asset('js/app.js')}}"></script>
<script>
    Echo.channel('testChannel').listen('TestEvent',(e)=>{
        console.log('akh');
        console.log(e.name);

    })
</script>
</body>
</html>
