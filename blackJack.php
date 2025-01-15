<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <?php
            session_start();

            $deck = [
                '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6,
                '7' => 7, '8' => 8, '9' => 9, '10' => 10, 'J' => 10,
                'Q' => 10, 'K' => 10, 'A' => 11
            ];

            $suits = [
                'clubs' => '♣', 
                'diamonds' => '♦', 
                'hearts' => '♥', 
                'spades' => '♠'
            ];

            $playerHand = [];
            $dealerHand = [];
            $playerHand = $_SESSION['playerHand'];
            $dealerHand = $_SESSION['dealerHand'];
            $fullDeck = [];
            $fullDeck = $_SESSION['fullDeck'];

            if (!isset($_SESSION['fullDeck']) || empty($_SESSION['fullDeck'])) {
                shuffleDeck(); // Tworzy nową talię
                $_SESSION['fullDeck'] = $fullDeck;
            } else {
                $fullDeck = $_SESSION['fullDeck'];
            }

            function shuffleDeck(){
                global $deck, $suits, $fullDeck;
                
                foreach ($deck as $value => $points) {
                    foreach ($suits as $suitName => $symbol) {
                        $fullDeck[] = $value . $symbol;
                    }
                }
                shuffle($fullDeck);
                shuffle($fullDeck);
            }
           
            
            function drawCard() {
                if (isset($_SESSION['fullDeck']) && count($_SESSION['fullDeck']) > 0) {
                    $card = array_shift($_SESSION['fullDeck']);
                    $_SESSION['fullDeck'] = array_values($_SESSION['fullDeck']);
                    return $card;
                }
                return null;
            }  
            
            function handCount($hand, $deck){
                global $deck;
                $sum = 0;
                $aces = 0;

                foreach($hand as $card){
                    $cardValue = mb_substr($card, 0, -1);
                    $sum += $deck[$cardValue];
                
                    if ($cardValue == 'A'){
                        $aces++;
                    }
                }

                while($aces > 0 && $sum + 10 <=21){
                    $sum += 10;
                    $aces--;
                }

                return $sum;

            }

            if (isset($_POST['action']) && $_POST['action'] == 'new_game') {
                shuffleDeck();
                $_SESSION['playerHand'] = [drawCard(), drawCard()];
                $_SESSION['dealerHand'] = [drawCard(), drawCard()];
                $_SESSION['gameOver'] = false;
            }

            if (!isset($_SESSION['playerHand']) || !isset($_SESSION['dealerHand'])) {
                shuffleDeck();
                $_SESSION['playerHand'] = [drawCard(), drawCard()];
                $_SESSION['dealerHand'] = [drawCard(), drawCard()];
                $_SESSION['gameOver'] = false;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST['action'] == 'hit') {
                    $newCard = drawCard();
                    $_SESSION['playerHand'][] = $newCard; 
                    // echo "Nowa karta gracza: " . $newCard . "<br>";
                } elseif ($_POST['action'] == 'stand') {
                    if (isset($_SESSION['playerHand']) && isset($_SESSION['dealerHand'])) {
                        while(handCount($_SESSION['dealerHand'], $deck) < 17) {
                            $_SESSION['dealerHand'][] = drawCard();
                        }
                        $_SESSION['gameOver'] = true;  // Gra zakończona
                    } else {
                        // Jeśli ręka gracza lub krupiera nie istnieje, możemy dać komunikat o błędzie
                        $error = "Ręce gracza lub krupiera nie zostały poprawnie zainicjowane.";
                    }
                }
            }
            
        ?>

        <div class="main">
            <h1>BLACK JACK</h1>

            <div class="hand">         
                <div class="dealerHand">    
                    <?php
                        $count = 0;
                        foreach ($_SESSION['dealerHand'] as $card) {
                            if($count == 0 && !$_SESSION['gameOver']){
                                echo "<div class='card flip hidden-card'></div>";
                                $count++;
                            }else{
                                $cardSuit = mb_substr($card, -1);
                                $cardValue = mb_substr($card, 0, -1);
                                if ($cardSuit == '♦' || $cardSuit == '♥') {
                                    $colorClass = 'red-card';
                                } else {
                                    $colorClass = 'black-card'; 
                                }
                                echo "<div class='card flip $colorClass' data-value='$cardValue' data-suit='$cardSuit'>$cardSuit</div>";
                            }
                        }
                    ?>
                </div>
                <h3>Dealer Cards</h3>   
                <h3>Your Cards</h3>
                <div class="playerHand">
                    <?php
                        foreach ($_SESSION['playerHand'] as $card) {
                            $cardSuit = mb_substr($card, -1);
                            $cardValue = mb_substr($card, 0, -1);
                            if ($cardSuit == '♦' || $cardSuit == '♥') {
                                $colorClass = 'red-card'; 
                            } else {
                                $colorClass = 'black-card';
                            }
                            echo "<div class='card $colorClass' data-value='$cardValue' data-suit='$cardSuit'>$cardSuit</div>";
                        }
                    ?>
                </div>
            </div>

            <form method="POST">
                <button type="submit" name="action" value="hit">HIT</button>
                <button type="submit" name="action" value="stand">STAND</button>
            </form>
            <div class="overlay" style="display: <?php echo $_SESSION['gameOver'] ? 'block' : 'none'?>"></div>
            <div class="pop-up" style="visibility: <?php echo $_SESSION['gameOver'] ? 'visible' : 'hidden'; ?>;">
                <?php
                    if ($_SESSION['gameOver']) {
                        $playerSum = handCount($_SESSION['playerHand'], $deck);
                        $dealerSum = handCount($_SESSION['dealerHand'], $deck);
            
                        if ($playerSum > 21) {
                            $result = "<h2>DEFEAT</h2><p>Dealer wins. You busts!</p>";
                        } elseif ($dealerSum > 21) {
                            $result = "<h2>VICTORY</h2><p>Dealer busts! You wins.</p>";
                        } elseif ($playerSum > $dealerSum) {
                            $result = "<h2>VICTORY</h2><p>You wins.</p>";
                        } elseif ($dealerSum > $playerSum) {
                            $result = "<h2>DEFEAT</h2><p>Dealer wins.</p>";
                        } else {
                            $result = "<p>Push! It's a tie.</p>";
                        }
        
                        echo $result;
                    }
                ?>
                <form method="post">
                    <button type="submit" name="action" value="new_game">NEW GAME</button>
                </form>
            </div>
        </div>
    </body>
</html>
