<?php

/**
 * Admin repository.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *  Class AdminRepository.
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<User>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class AdminRepository extends ServiceEntityRepository
{
    /**
     * Constructor for the User Repository.
     *
     * @param ManagerRegistry             $registry       the ManagerRegistry service instance managing entities
     * @param PaginatorInterface          $paginator      the PaginatorInterface service for pagination operations
     * @param UserPasswordHasherInterface $passwordHasher the UserPasswordHasherInterface service for hashing user passwords
     */
    public function __construct(ManagerRegistry $registry, private readonly PaginatorInterface $paginator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Save user entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $em = $this->getEntityManager();

        $em->persist($user);
        $em->flush();
    }

    /**
     * Update user entity.
     *
     * @param User $user User entity
     */
    public function update(User $user): void
    {
        $em = $this->getEntityManager();

        $em->persist($user);
        $em->flush();
    }

    /**
     * Updates the password of a user entity and persists it.
     *
     * @param User   $user          The user entity for which to update the password
     * @param string $plainPassword The plain password to hash and set for the user
     */
    public function updatePassword(User $user, string $plainPassword): void
    {

        $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $this->update($user);
    }

    /**
     * Counts admins other to the one logged in.
     *
     * @param User $editedUser The current user
     *
     * @return int $count
     */
    public function countOtherAdmins(User $editedUser): int
    {
        $users = $this->findAll();

        $count = 0;

        foreach ($users as $user) {
            if ($user->getId() !== $editedUser->getId()
                && in_array('ROLE_ADMIN', $user->getRoles(), true)
            ) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Retrieves paginated users from the database.
     *
     * @param int $page  The current page number
     * @param int $limit The number of users per page
     *
     * @return PaginationInterface The paginator object containing the users
     */
    public function getPaginatedUsers(int $page = 1, int $limit = 10): PaginationInterface
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        return $this->paginator->paginate($queryBuilder, $page, $limit);
    }

    /**
     * Finds all users.
     *
     * @return User[] User array
     */
    public function findAllUsers(): array
    {
        return $this->findAll();
    }
}
