<?php

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\Equipment;
use App\Entity\Rental;
use App\Entity\Reservation;
use App\Entity\Season;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // Propriété pour encoder les mots de passe
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {        
        // Appel de la méthode pour générer des utilisateurs
        $this->loadUsers($manager);

        // Appel de la méthode pour générer des équipements
        $this->loadEquipments($manager);

        // Appel de la méthode pour générer des saisons
        $this->loadSeasons($manager);

        // Appel de la méthode pour générer des types de location
        $this->loadTypes($manager);

        // Appel de la méthode pour générer des locations
        $this->loadRentals($manager);

        // Appel de la méthode pour générer des disponibilités
        $this->loadAvailabilities($manager);

        // Appel de la méthode pour générer des réservations
        $this->loadReservations($manager);

        $manager->flush();
    }

    /**
     * Méthode pour générer des utlisateurs
     * @param ObjectManager $manager
     * @return void
     */
    public function loadUsers(ObjectManager $manager): void
    {
        // Création d'un tableau avec les infos des utilisateurs
        $array_users = [
            [
                'email' => "admin@admin.com",
                'password' => 'admin',
                'roles' => ['ROLE_ADMIN'],
                'firstname' => 'Admin',
                'lastname' => 'Admin',
                'username' => 'administrateur'
            ],
            [
                'email' => "john@doe.com",
                'password' => 'JohnDoe1234',
                'roles' => ['ROLE_USER'],
                'firstname' => 'John',
                'lastname' => 'Doe',
                'username' => 'johndoe'
            ]
        ];

        // Boucle pour créer les utilisateurs
        foreach ($array_users as $key => $value) {
            $user = new User();
            $user->setEmail($value['email']);
            $user->setPassword($this->encoder->hashPassword($user, $value['password']));
            $user->setRoles($value['roles']);
            $user->setFirstname($value['firstname']);
            $user->setLastname($value['lastname']);
            $user->setUsername($value['username']);

            // Sauvegarde de l'utilisateur
            $manager->persist($user);

            // Définition des références
            $this->addReference('user_' . $key, $user);
        }
    }

    /**
     * Méthode pour générer des équipements
     * @param ObjectManager $manager
     * @return void
     */
    public function loadEquipments(ObjectManager $manager): void
    {
        // Création d'un tableau avec les équipements disponibles dans une location
        $array_equipments = [
            [
                'label' => 'Climatisation / Chauffage'
            ],
            [
                'label' => 'Cuisine équipée'
            ],
            [
                'label' => 'Télévision'
            ],
            [
                'label' => 'Wi-Fi'
            ],
            [
                'label' => 'Proche Parking'
            ],
            [
                'label' => 'Proche Piscine'
            ],
            [
                'label' => 'Animaux acceptés'
            ],
            [
                'label' => 'Salon de jardin'
            ],
            [
                'label' => 'Terrasse'
            ],
            [
                'label' => 'Place ombragée'
            ],
            [
                'label' => 'Borne électrique'
            ],
            [
                'label' => 'Borne eau'
            ]
        ];

        // Boucle pour créer les équipements
        foreach ($array_equipments as $key => $value) {
            $equipment = new Equipment();
            $equipment->setLabel($value['label']);

            // Sauvegarde de l'équipement
            $manager->persist($equipment);

            // Définition des références
            $this->addReference('equipment_' . $key, $equipment);
        }
    }

    /**
     * Méthode pour générer des saisons
     * @param ObjectManager $manager
     * @return void
     */
    public function loadSeasons(ObjectManager $manager): void
    {
        // Création d'un tableau avec les saisons
        $array_seasons = [
            [
                'label' => 'Basse saison',
                'isClosed' => false,
                'percentage' => 80,
                'dateStart' => new \DateTime('2025-04-01'),
                'dateEnd' => new \DateTime('2025-06-30')
            ],
            [
                'label' => 'Basse saison',
                'isClosed' => false,
                'percentage' => 80,
                'dateStart' => new \DateTime('2025-09-01'),
                'dateEnd' => new \DateTime('2025-09-30')
            ],
            [
                'label' => 'Haute saison',
                'isClosed' => false,
                'percentage' => 120,
                'dateStart' => new \DateTime('2025-07-01'),
                'dateEnd' => new \DateTime('2025-08-31')
            ],
            [
                'label' => 'Saison fermée',
                'isClosed' => true,
                'percentage' => 0,
                'dateStart' => new \DateTime('2025-01-01'),
                'dateEnd' => new \DateTime('2025-03-31'),
            ],
            [
                'label' => 'Saison fermée',
                'isClosed' => true,
                'percentage' => 0,
                'dateStart' => new \DateTime('2025-10-01'),
                'dateEnd' => new \DateTime('2025-12-31'),
            ]
        ];

        // Boucle pour créer les saisons
        foreach ($array_seasons as $key => $value) {
            $season = new Season();
            $season->setLabel($value['label']);
            $season->setClosed($value['isClosed']);
            $season->setpercentage($value['percentage']);
            $season->setDateStart($value['dateStart']);
            $season->setDateEnd($value['dateEnd']);

            // Sauvegarde de la saison
            $manager->persist($season);

            // Définition des références
            $this->addReference('season_' . $key, $season);
        }
    }

    /**
     * Méthode pour générer des types de location
     * @param ObjectManager $manager
     * @return void
     */
    public function loadTypes(ObjectManager $manager): void
    {
        // Création d'un tableau avec les types de location
        $array_types = [
            [
                'label' => 'Emplacement nu petite taille 4 personnes',
                'imagePath' => 'emplacement-nu-petite-taille.jpg',
                'price' => 1000
            ],
            [
                'label' => 'Emplacement nu grande taille 6 personnes',
                'imagePath' => 'emplacement-nu-grande-taille.jpg',
                'price' => 1500
            ],
            [
                'label' => 'Tente meublée 2 places',
                'imagePath' => 'tente-meublee-2-places.jpg',
                'price' => 2000
            ],
            [
                'label' => 'Tente meublée 4 places',
                'imagePath' => 'tente-meublee-4-places.jpg',
                'price' => 2500
            ],
            [
                'label' => 'Mobil-home 4 places',
                'imagePath' => 'mobil-home-4-places.jpg',
                'price' => 3000
            ],
            [
                'label' => 'Mobil-home 8 places',
                'imagePath' => 'mobil-home-8-places.jpg',
                'price' => 3500
            ]
        ];

        // Boucle pour créer les types de location
        foreach ($array_types as $key => $value) {
            $type = new Type();
            $type->setLabel($value['label']);
            $type->setImagePath($value['imagePath']);
            $type->setPrice($value['price']);

            // Sauvegarde du type de location
            $manager->persist($type);

            // Définition des références
            $this->addReference('type_' . $key, $type);
        }
    }

    /**
     * Méthode pour génére des locations
     * @param ObjectManager $manager
     * @return void
     */
    public function loadRentals(ObjectManager $manager): void
    {
        // Création d'un tableau avec les locations
        $array_rentals = [
            [
                'title' => 'Emplacement nu petite taille 4 personnes',
                'description' => 'Emplacement nu petite taille pour 4 personnes maximum',
                'bedding' => 4,
                'surface' => 10,
                'location' => 1,
                'type' => 0,
                'equipment' => [5, 7, 10],
                'isClean' => true
            ],
            [
                'title' => 'Emplacement nu grande taille 6 personnes',
                'description' => 'Emplacement nu grande taille pour 6 personnes maximum',
                'bedding' => 6,
                'surface' => 15,
                'location' => 2,
                'type' => 1,
                'equipment' => [5, 6, 10, 11],
                'isClean' => true
            ],
            [
                'title' => 'Tente meublée 2 places',
                'description' => 'Tente meublée pour 2 personnes',
                'bedding' => 2,
                'surface' => 20,
                'location' => 3,
                'type' => 2,
                'equipment' => [5, 7, 10, 11],
                'isClean' => true
            ],
            [
                'title' => 'Tente meublée 4 places',
                'description' => 'Tente meublée pour 4 personnes',
                'bedding' => 4,
                'surface' => 25,
                'location' => 4,
                'type' => 3,
                'equipment' => [6, 7, 10, 11],
                'isClean' => true
            ],            
            [
                'title' => 'Tente meublée 4 places',
                'description' => 'Tente meublée pour 4 personnes',
                'bedding' => 4,
                'surface' => 25,
                'location' => 5,
                'type' => 3,
                'equipment' => [6, 7, 10, 11],
                'isClean' => true
            ],
            [
                'title' => 'Mobil-home 4 places',
                'description' => 'Mobil-home pour 4 personnes',
                'bedding' => 4,
                'surface' => 30,
                'location' => 6,
                'type' => 4,
                'equipment' => [0, 1, 2, 3, 6, 8],
                'isClean' => true
            ],
            [
                'title' => 'Mobil-home 8 places',
                'description' => 'Mobil-home pour 8 personnes',
                'bedding' => 8,
                'surface' => 35,
                'location' => 7,
                'type' => 5,
                'equipment' => [0, 1, 2, 3, 5, 6, 7, 8],
                'isClean' => true
            ]
        ];

        // Boucle pour créer les locations
        foreach ($array_rentals as $key => $value) {
            $rental = new Rental();
            $rental->setTitle($value['title']);
            $rental->setDescription($value['description']);
            $rental->setBedding($value['bedding']);
            $rental->setSurface($value['surface']);
            $rental->setLocation($value['location']);
            $rental->setType($this->getReference('type_' . $value['type']));
            $rental->setClean($value['isClean'] ?? false);

            // Boucle pour ajouter les équipements aux locations
            foreach ($value['equipment'] as $equipment) {
                $rental->addEquipment($this->getReference('equipment_' . $equipment));
            }

            // Sauvegarde de la location
            $manager->persist($rental);

            // Définition des références
            $this->addReference('rental_' . $key, $rental);
        }
    }

    /**
     * Méthode pour générer les disponibilités (les dates rentrées sont celles non disponibles)
     * @param ObjectManager $manager
     * @return void
     */
    public function loadAvailabilities(ObjectManager $manager): void
    {
        // Création de tableaux avec les disponibilités

        $array_availabilities = [
            [
                'rental' => 0,
                'startDate' => new \DateTime('2025-05-01'),
                'endDate' => new \DateTime('2025-05-10')
            ],
            [
                'rental' => 1,
                'startDate' => new \DateTime('2025-06-05'),
                'endDate' => new \DateTime('2025-06-07')
            ]
        ];

        // Boucles pour créer les disponibilités
        foreach ($array_availabilities as $key => $value) {
            $availability = new Availability();
            $availability->setRental($this->getReference('rental_' . $value['rental']));
            $availability->setDateStart($value['startDate']);
            $availability->setDateEnd($value['endDate']);

            // Sauvegarde de la disponibilité
            $manager->persist($availability);
        }
    }

    /**
     * Méthode pour générer des réservations
     * @param ObjectManager $manager
     * @return void
     */
    public function loadReservations(ObjectManager $manager): void
    {
        // Création d'un tableau avec les réservations
        $array_reservations = [
            [
                'rental' => 0,
                'user' => 1,
                'startDate' => new \DateTime('2025-04-01'),
                'endDate' => new \DateTime('2025-04-30'),
                'adultsNumber' => 2,
                'kidsNumber' => 0,
                'price' => 30000
            ]
        ];

        // Boucle pour créer les réservations
        foreach ($array_reservations as $key => $value) {
            $reservation = new Reservation();
            $reservation->setRental($this->getReference('rental_' . $value['rental']));
            $reservation->setUser($this->getReference('user_' . $value['user']));
            $reservation->setDateStart($value['startDate']);
            $reservation->setDateEnd($value['endDate']);
            $reservation->setAdultsNumber($value['adultsNumber']);
            $reservation->setKidsNumber($value['kidsNumber']);
            $reservation->setPrice($value['price']);

            // Sauvegarde de la réservation
            $manager->persist($reservation);
        }
    }
}
