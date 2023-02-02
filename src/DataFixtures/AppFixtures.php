<?php

namespace App\DataFixtures;

use App\Entity\Atelier;
use App\Entity\Event;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@api.com");
        $user->setRoles(["ROLE_USER"]);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);
        
        $admin = new User();
        $admin->setEmail("admin@api.com");
        $admin->setRoles(["ROLE_ADMIN"]);

        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
        $manager->persist($admin);




        $AtelierList = [];
        for ($i = 0; $i < 2; $i++) {
            $atelier = new Atelier();
            $atelier->setName("Atelier n°" . $i);
            $atelier->setLocation("2 rue ...");
            $atelier->setCoordinate("32 25 684");
            $manager->persist($atelier);
            
            
            $AtelierList[] = $atelier;
            
        }
        
        for ($i = 0; $i < 25; $i++) { 
            $event = new Event();
            $event->setTitle("Titre n°" . $i);
            $event->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
            $event->setDateStart(new DateTime((string)("2023-02-" . $i)));
            $event->setTimeStart(DateTime::createFromFormat("H:i", "12:15"));
            $event->setDateEnd(new DateTime((string)("2023-02-" . ($i+1))));
            $event->setTimeEnd(DateTime::createFromFormat("H:i", "15:00"));
            $event->setPriority(0);
            $event->setCompulsoryPresence(false);
            $event->setAtelier($AtelierList[array_rand($AtelierList)]);

            $manager->persist($event);
        }
        
        $manager->flush();
    }
}
