<!DOCTYPE html>
<?php
/*
    Author: Silvia Mariana Reyesvera Quijano - 000813686 
    Date: October 4th, 2020

    This code code validates the input from the fortune cookies display settings
    If there is errors, they are displayed
    Otherwise it displays the fortune cookies
*/

/* Retrieve variables */
$name = filter_input(INPUT_GET, "name", FILTER_SANITIZE_SPECIAL_CHARS);
$amount = filter_input(INPUT_GET, "amount", FILTER_VALIDATE_INT);
$min = filter_input(INPUT_GET, "min", FILTER_VALIDATE_INT);
$max = filter_input(INPUT_GET, "max", FILTER_VALIDATE_INT);
/* 
    Source for how to form of a regular expresions: https://www.w3schools.com/php/filter_validate_regexp.asp
    Source for regular expression for hex colors: https://stackoverflow.com/questions/12837942/regex-for-matching-css-hex-colors
*/
$color = filter_input(INPUT_GET, "color", FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/#([a-f0-9]{3}){1,2}\b/i")));

/* $errors saves all the error messages to be desplayed afterwards */
$errors = [];

// name validation
if ($name === null) {
    array_push($errors, "You have to enter a name");
} elseif ($name === "" || strlen($name) > 40) {
    array_push($errors, "The name has to have at least one and no more than 40 characters");
}

// amount validation
if ($amount === false) {
    array_push($errors, "You didn't enter a valid number");
} elseif ($amount === null) {
    array_push($errors, "You have to enter an amount of cookies");
} elseif ($amount <= 0 || $amount > 50) {
    array_push($errors, "The amount has to be between 1 and 50");
}

// min and max validation
// checking for min and max being null or false
// note some ifs are repeated within the if-else in order to provide a precise message in various circumstances
if ($min === false || $min === null || $max === false || $max === null) {

    if ($min === false) {
        array_push($errors, "You didn't enter a valid minimum for your lucky numbers range");
    } elseif ($min === null) {
        array_push($errors, "You have to enter a minimum for your lucky number range");
    } elseif ($min < 0 || $min > 994) { // checking min range
        array_push($errors, "The minimum in your lucky number range has to be between 0 and 994");
    }


    if ($max === false) {
        array_push($errors, "You didn't enter a valid maximum for your lucky numbers range");
    } elseif ($max === null) {
        array_push($errors, "You have to enter a maximum for your lucky number range");
    } elseif ($max <= 5 || $max > 1000) { // checkin max range
        array_push($errors, "The maximum in your lucky number range has to be between 6 and 1000");
    }
} else {
    if ($min < 0 || $min > 994) { // checking min range
        array_push($errors, "The minimum in your lucky number range has to be between 0 and 994");
    }
    if ($max <= 5 || $max > 1000) { // checkin max range
        array_push($errors, "The maximum in your lucky number range has to be between 6 and 1000");
    }

    if ($min > $max) { // checking max is smaller than min
        array_push($errors, "You can't have a minimum larger than a maximum for your lucky numbers range");
    } elseif ($max - $min < 6) { // checking there are at least 7 unique numbers in the specified range
        array_push($errors, "The range of numbers doesn't allow for unique lucky numbers (there are 7 lucky numbers)");
    }
}
// color validation
if ($color === false) {
    array_push($errors, "You didn't enter a valid color value (hex format)");
} elseif ($color === null) {
    array_push($errors, "You have to enter a color");
}
?>
<html>

<head>
    <title>Fortune Cookies!</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="cookiesstyles.css">
    <style>
        #cookieContainer {
            /* sets cookies color to the chosen one */
            background-color: <?= $color ?>;
        }
    </style>
</head>

<body>
    <?php
    /* check if there are any error messages within $errors */
    if (count($errors) !== 0) { // loop to display error messages
    ?>
        <div id="errorContainer">
            <h3>Note the following errors:</h3>
            <ul>
                <?php
                foreach ($errors as $error) {
                    echo "<li>$error</li>"; // list the errors
                }
                ?>
            </ul>
        </div>

    <?php
    } else {
        $cookies = []; // create array to store all of the cookies
        /*
            Shuffle the messages static variable from the class FortuneCookie.
            The messages are retrieved in a for loop by index number.
            Shuffling the messages allows for a different set of messages each time.
        */
        FortuneCookie::shuffleMessages();
        for ($i = 0; $i < $amount; $i++) {
            /* Index is sent as well because it is used to retrieve a message from the message array */
            array_push($cookies, new FortuneCookie($i, $min, $max)); // create cookie and store it in array
        }

    ?>

        <div id="header">
            <!-- Display Name -->
            <img src="images/cookieJar1.png" alt="Cookie Jar">
            <h1><?= $name ?></h1>
        </div>
        <div id="resultFlexBox">

            <?php
            /* for loop used to display the cookies */
            for ($i = 0; $i < $amount; $i++) {

                echo "<div id='cookieContainer'>";
                $luckyNums = []; // temporarily stores the current cookie's lucky numbers
                $luckyNums = $cookies[$i]->get_numbers();
            ?>
                <!-- 
                    There are two images, one is shown when not hovering over the section (unbroken cookie),
                    the second one is shown once hovering over the section (broken cookie with message)
                 -->
                <img id="unbrokenCookie" src="images/notBrokenCookie1.png" alt="Fortune Cookie">
                <img id="brokenCookie" src="images/brokenCookie1.png" alt="Broken Fortune Cookie">
                <!-- Display the message over the broken cookie image -->
                <div id="fortuneMessage"><?= $cookies[$i]->get_message() ?><br><br>

                    Lucky Numbers:<br>

                <?php
                /* Display the lucky numbers from the array that stores them */
                echo " $luckyNums[0] $luckyNums[1] $luckyNums[2] $luckyNums[3] $luckyNums[4] $luckyNums[5] $luckyNums[6]</div>";
                echo "</div>";
            }
                ?>
                </div>

            <?php
        }
            ?>
</body>
<?php

/*
    This class represents a fortune cookie with a selected message 
    from a list (stored in a static variable)
    and a set of random lucky numbers within a specified range
*/
class FortuneCookie
{
    // source of messages: https://theletteredcottage.net/testsite/wp-content/uploads/2012/02/FORTUNE-COOKIE-PRINTABLE.pdf
    // fortune cookie's available messages
    private static $messages = [
        "you are loved",
        "happy news is on its way to you",
        "a thrilling time is in your immediate future",
        "a pleasant surprise is waiting for you",
        "courtesy is contagious",
        "depart not from the path which fate has assigned you",
        "good news will come to you by mail",
        "miles are covered one step at a time",
        "share your joys and sorrows with your family",
        "welcome change",
        "you are in good hands",
        "your difficulties will strengthen you",
        "don't let your limitations overshadow your talent",
        "every wise man started out by asking many questions",
        "never lose the ability to find beauty in the ordinary",
        "see the light at the end of the tunnel",
        "integrity is the essence of everything succesful",
        "be creative when inventing your life",
        "be led by your dreams",
        "you will be hungry again in one hour",
        "a closed mouth gathers no feet",
        "patience is key",
        "do something spontaneous",
        "good luck is the result of good planning",
        "'welcome' is a powerful word",
        "all your hard work will soon pay off",
        "determination is what you need now",
        "do not make extra work for yourself",
        "have a beautiful day",
        "now is the time to try something new",
        "take the high road",
        "when your heart is pure, your mind is clear",
        "you will be successful in your work",
        "your ideals are well within your reach",
        "emulate what you respect in your friends",
        "good things come to those who wait",
        "nature, time and patience are the 3 best doctors",
        "fear is just excitement in need of an attitude adjustment",
        "follow your dreams- you can do anything!",
        "your smile will tell you what makes you feel good",
        "the smallest deed is better than the biggest intention",
        "thanks for letting me out of that cookie!", // favorite one
        "don't mistake temptation for opportunity",
        "don't wait for your ship to come in, swim to it",
        "may life throw a pleasant curve",
        "look for new outlets for your creative abilities",
        "a fresh start will put you on your way",
        "change is happening, go with the flow",
        "congrats! you're on your way",
        "don't just spend time, invest it",
        "he who knows he has enough, is rich",
        "practice makes perfect",
        "the best prediction of the future is the past",
        "you are almost there",
        "your ability is appreciated",
        "don't just think- act!",
        "every flower blooms in its own sweet time",
        "go with your gut",
        "a warm smile is a testimony of a generous nature",
        "the dream is within you",
        "people like your smile",
        "the fortune you seek is in another cookie",
        "think you can, think you can't either way, you're right",
        "keep your words sweet, in case you have to eat them",
        "you will have a fun adventure",
        "the best is yet to come"

    ];
    private $message; // fortune cookie's message
    private $numbers = []; // fortune cookie's lucky numbers

    /*
        Class constructor
        Sets message from the one stored in the specified position within the list of messages
        Obtains a set of 7 unique lucky numbers within the specified range (min, max)
        Sorts the lucky numbers, so they stay in order within the array
    */
    public function __construct($position, $min, $max)
    {
        $this->message = self::$messages[$position];

        for ($i = 0; $i < 7; $i++) {
            $num = rand($min, $max);
            // checks if the random number is already in the array and
            // while this is true it looks for another random number within the range
            while (in_array($num, $this->numbers)) {
                $num = rand($min, $max);
            }
            $this->numbers[$i] = $num; // once the number is unique in the array it adds it
        }
        sort($this->numbers); // sort the array
    }

    /*
        Shuffles the $messages array
        No return
    */
    public static function shuffleMessages()
    {
        shuffle(self::$messages);
    }

    /*
        Returns fortune cookie's message
    */
    public function get_message()
    {
        return $this->message;
    }

    /*
        Returns fortune cookie's lucky numbers array (numbers)
    */
    public function get_numbers()
    {
        return $this->numbers;
    }
}

?>
<footer>
    <p>
        Background creator application: flaticon.com/pattern/<br>
        Fortune cookies messages: https://theletteredcottage.net/testsite/wp-content/uploads/2012/02/FORTUNE-COOKIE-PRINTABLE.pdf
    </p>
</footer>

</html>