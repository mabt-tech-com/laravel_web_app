<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification</title>

    <style type='text/css'>

    </style>
</head>

<body>


    <div style="width:800px; height:600px; padding:20px; text-align:center; border: 10px solid #787878">
        <div style="width:750px; height:550px; padding:20px; text-align:center; border: 5px solid #787878">
            <span style="font-size:50px; font-weight:bold">Certificat</span>
            <br><br>
            <span style="font-size:25px"><i>Nous certifions par la présente que</i></span>
            <br><br>
            <span style="font-size:30px"><b>{{ $user_full_name }}</b></span><br /><br />
            <span style="font-size:25px"><i>a suivi et complété avec succès la formation intitulée :</i></span>
            <br /><br />
            <span style="font-size:30px">{{ $training_label }}</span> <br /><br />
            <span style="font-size:20px">avec un score de<b>90%</b></span> <br /><br /><br /><br />
            <span style="font-size:25px"><i>le</i></span><br>
            <span style="font-size:30px">{{ now() }}</span> <br /><br />
        </div>
    </div>

</body>

</html>
