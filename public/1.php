<?php
echo "
<body>
<form action='1.php' method='POST'>
    <input type='text' name='name' />
    <input type='submit'  value='Отправить'>
</form>
</body>
</html>";
$name = "не определено";
$age = "не определен";
if(isset($_POST["name"])){
    $name = $_POST["name"];
}
echo "Имя: $name ";
