<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Card;
use App\Entity\User;
use App\Entity\Review;
use DateTimeImmutable;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Provider\OkemonProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{  
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    } 
    
    /**
     * Load data fixtures with the passed EntityManager
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {   

        // Create instance of faker generator
        $faker = Factory::create('fr_FR');

        // Give our Provider to faker
        $faker->addProvider(new OkemonProvider());

        //Create user array containing all users (we will need this array for comments fixtures):
        $user_array = [];
        //Create review array containing all reviews (we will need this array for comments fixtures):
        $review_array = [];
        //Create card array containing all cards (we will need this array for reviews fixtures):
        $card_array = [];


        // Create one user with admin role
        // Instanciate a new User object
        $user = new User();

        // Set $user properties
        $user->setNickname("admin");
        $user->setEmail("admin@admin.com");
        $user->setPassword($this->passwordHasher->hashPassword($user, "admin"));
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setAge(mt_rand(16, 80));
        $user->setCountry("France");
        $user->setDescription("Je suis un super admin");
        $user->setCatchphrase("Soyez Admin, pas user!");
        $user->setImage($faker->imageRandom());
        $user->setStatus(1);

        // Persist the object
        $manager->persist($user);


        // Create one user with user role
        // Instanciate a new User object
        $user = new User();

        // Set $user properties
        $user->setNickname("user");
        $user->setEmail("user@user.com");
        $user->setPassword($this->passwordHasher->hashPassword($user, "user"));
        $user->setRoles(["ROLE_USER"]);
        $user->setAge(mt_rand(16, 80));
        $user->setCountry("USA");
        $user->setDescription("No comment !");
        $user->setCatchphrase("Un jour je serais le meilleur dresseur !");
        $user->setImage($faker->imageRandom());
        $user->setStatus(1);

        // Persist the object
        $manager->persist($user);

        // Create 1500 random card from provider
        for ($i = 0; $i < 1500; $i++) {

            // Instanciate a new Card object
            $card = new Card();


            // set $card properties
            $card->setApiId($faker->apiIdFixtures());
         
            $card->setOwn(mt_rand(0,1));
            if ($card->getOwn() === 0){
                $card->setWish(1);
            }else{
                $card->setWish(0);
            }
            
            
            //Push cards in $card_array
            array_push($card_array, $card);

             // Persist the object
             $manager->persist($card);


        }
        
        // Create 30 users 
        for ($i = 0; $i < 30; $i++) {

            // Create a variable containing a random number between 0 and 3. This number is use then to determine how many reviews the user will write
            $numberOfReview = (mt_rand(0,2));

            // Instanciate a new User object
            $user = new User();

            // Set $user properties
            $user->setNickname($faker->firstName());
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, "password"));
            $user->setCountry("France");
            $user->setDescription("On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même. L'avantage du Lorem Ipsum sur un texte générique comme 'Du texte. Du texte. Du texte.' est qu'il possède une distribution de lettres plus ou moins normale, et en tout cas comparable avec celle du français standard. De nombreuses suites logicielles de mise en page ou éditeurs de sites Web ont fait du Lorem Ipsum leur faux texte par défaut, et une recherche pour 'Lorem Ipsum' vous conduira vers de nombreux sites qui n'en sont encore qu'à leur phase de construction. Plusieurs versions sont apparues avec le temps, parfois par accident, souvent intentionnellement (histoire d'y rajouter de petits clins d'oeil, voire des phrases embarassantes).");
            $user->setCatchphrase("Attrapez les tous !");
            $user->setImage($faker->imageRandom());
            $user->setAge(mt_rand(16, 80));
            $user->setStatus(mt_rand(1,2));

            // Randomly add reviews write by this user (depending on $numberOfReview value)

            while ($numberOfReview > 0){
                $user->addReview($this->createReview($card_array));
                $numberOfReview--;
            }

            //Push user in $user_array
            array_push($user_array, $user);

            //Push review in $review_array
            foreach ($user->getReview() as $review) {
                array_push($review_array, $review);
            }

            // Persist the object
            $manager->persist($user);

        }

        // associate each card with user
        foreach ($card_array as $card) {
            $card->addUser($user_array[array_rand($user_array, 1)]);
        }
        
        // Create 100 comments

        for ($i = 0; $i < 100; $i++) {
            // Instanciate a new Comment object
            $comment = new Comment();

            // Set $comment properties
            $comment->setContent($faker->paragraph(2));// The parameter 2 mean that I get a 2sentences paragraph

            // Randomly associate this comment with one user(using array_rand to randomly pick one user from $user_array)
            $comment->setUser($user_array[array_rand($user_array, 1)]);
            // Randomly associate this comment with one review(using array_rand to randomly pick one review from $review_array)
            $comment->setReview($review_array[array_rand($review_array, 1)]);

            // Persist the object
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function createReview(array $card_array)
    {   
        // Create instance of faker generator
        $faker = Factory::create('fr_FR');
        // Instanciate a new Review object
        $review = new Review();

        // Set $review properties
        $review->setTitle($faker->sentences(1, true));// The parameter 1 mean that I get 1 sentence, the parameter true mean that I want a string to be return(instead of an array by default)
        $review->setContent($faker->paragraphs(2, true));// The parameter 2 mean that I get 2 paragraphs, the parameter true mean taht I want a string to be return(instead of an array by default)
        $review->setCard($card_array[array_rand($card_array, 1)]);
        return $review;
    }

}