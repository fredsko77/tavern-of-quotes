<?php

namespace Admin\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $manager,
        private SluggerInterface $slugger,
        private Security $security,
        private UserRepository $repository,
        private PaginatorInterface $paginator
    ) {
    }

    /**
     * [Persist a user in database]
     *
     * @param User $user
     * @param string|null $password
     * 
     * @return void
     */
    public function create(User $user, ?string $password = null): void
    {
        $user->setCreatedAt(new DateTimeImmutable)
            ->setSlug($this->slugger->slug($user->getUsername()))
            ->setPassword($this->hasher->hashPassword($user, $password ?? 'magic'));

        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * [Edit a user in database]
     *
     * @param User $user
     * @param string|null $password
     * 
     * @return void
     */
    public function edit(User $user, ?string $password): void
    {

        $user->setCreatedAt(new DateTimeImmutable)
            ->setUpdatedAt(new DateTimeImmutable);
        if ($password) {
            $user->setPassword($this->hasher->hashPassword($user, $password));
        }

        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * [Get list of paginated user index page]
     *
     * @param Request $request
     * 
     * @return array
     */
    public function index(Request $request): array
    {
        $page = $request->query->getInt('page', 1);
        $nbItems = $request->query->getInt('nbItems', 10);
        $query = $request->query->get('query', '');

        $users = $this->paginator->paginate(
            $this->repository->findAdminUser(),
            $page,
            $nbItems
        );

        return compact('users', 'query');
    }

    /**
     * [Remove a user from database]
     *
     * @param User $user
     * 
     * @return void
     */
    public function delete(User $user): void
    {
        $this->manager->remove($user);
        $this->manager->flush();
    }
}
