<?php

        // create curl resource

        $ch = curl_init();



        // set url

        curl_setopt($ch, CURLOPT_URL, "google.com");



        //return the transfer as a string

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



        // $output contains the output string

       echo $output = curl_exec($ch);



        // close curl resource to free up system resources

        curl_close($ch);      

?>
