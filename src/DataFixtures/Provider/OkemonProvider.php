<?php

namespace App\DataFixtures\Provider;

class OkemonProvider
{
    // image png for fixture

    private $images = [
        "slowpoke_astronaut.png",
        "mewtwo_indian_dancer.png",
        "snorlax_matador.png",
        "ratatta_trader.png",
        "charmander_student.png",
        "pikachu_gangster.png",
        "squirtle_fireman.png",
        "evee_santa.png",
        "bulbasaure_farmer.png",
        "chansey_nurse.png",
    ];

    /**
     * Choose randomly one card for user fixture
     * @array $cards
     * @return string
     */
    public function apiIdFixtures()
    {
        $setSV = "sv3pt5-";
        $cards = [];

        for ($i = 1; $i <= 207; $i++) {
        $cards[] = $setSV . $i;
        }

        $setXY = "xy1-";
        for ($i = 1; $i <= 140; $i++) {
            $cards[] = $setXY . $i;
            }

        $setSW = "swsh1-";
        for ($i = 1; $i <= 180; $i++) {
            $cards[] = $setSW . $i;
            }
        return $cards[mt_rand(0,525)];
    }
    
    /**
     * Choose randomly one image for user fixture
     */
    public function imageRandom()
    {
        return $this->images[mt_rand(0,9)];
    }
}