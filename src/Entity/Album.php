<?php

/**
 * Album entity.
 */

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Album.
 *
 * @ORM\Entity(repositoryClass=AlbumRepository::class)
 *
 * @ORM\Table(name="albums")
 */
#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ORM\Table(name: 'albums')]
class Album
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 64)]
    private ?string $title = null;

    /**
     * Artist.
     */
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 64)]
    private ?string $artist = null;

    /**
     * Release date.
     *
     * @var DATE_MUTABLE|null
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $releaseDate = null;

    /**
     * Category.
     */
    #[ORM\ManyToOne]
    private ?Category $category = null;

    /**
     * Slug.
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;

    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'albums_tags')]
    private Collection $tags;

    /**
     * Author.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(User::class)]
    private ?User $author = null;

    /**
     * Comments.
     *
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'album', targetEntity: Comment::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    /**
     * Users who favorited this album.
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favorites')]
    private Collection $users;

    /**
     * Ratings.
     *
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(mappedBy: 'album', targetEntity: Rating::class, orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\OneToOne(mappedBy: 'album', cascade: ['persist', 'remove'])]
    private ?Cover $cover = null;

    /**
     * Album constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Setter for Id.
     *
     * @param int|null $id
     *
     * @return static returns the instance of the current class
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Getter for Title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for Title.
     *
     * @param string|null $title
     *
     * @return static returns the instance of the current class
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Getter for Artist.
     *
     * @return string|null Artist
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }

    /**
     * Setter for Artist.
     *
     * @param string|null $artist
     *
     * @return static returns the instance of the current class
     */
    public function setArtist(string $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Getter for Release date.
     *
     * @return DateTimeInterface|null Release date
     */
    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    /**
     * Setter for Release date.
     *
     * @param \DateTimeInterface|null $releaseDate release date
     *
     * @return static returns the instance of the current class
     */
    public function setReleaseDate(?\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Getter for Category.
     *
     * @return string|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for Category.
     *
     * @param string|null $category
     *
     * @return static returns the instance of the current class
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for Slug.
     *
     * @return string|null Slug
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Setter for Slug.
     *
     * @param string|null $slug
     *
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add a Tag to the album.
     *
     * @param Tag $tag the tag to add
     *
     * @return static returns the instance of the current class
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove a Tag from the album.
     *
     * @param Tag $tag the tag to remove
     *
     * @return static returns the instance of the current class
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Getter for Author.
     *
     * @return string|null the author of the album
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for Author.
     *
     * @param string|null $author the author of the album
     *
     * @return static returns the instance of the current class
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for Comments.
     *
     * @var Collection<int, Comment>
     *
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Add a Comment to the album.
     *
     * @param Comment $comment the comment to add
     *
     * @return static returns the instance of the current class
     */
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAlbum($this);
        }

        return $this;
    }

    /**
     * Remove a Comment from the album.
     *
     * @param Comment $comment the comment to remove
     *
     * @return static returns the instance of the current class
     */
    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAlbum() === $this) {
                $comment->setAlbum(null);
            }
        }

        return $this;
    }

    /**
     * Getter for Users who favorited this album.
     *
     * @var Collection<int, User>
     *
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Add a User to the list of users who favorited this album.
     *
     * @param User $user the user to add
     *
     * @return static returns the instance of the current class
     */
    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFavorite($this);
        }

        return $this;
    }

    /**
     * Remove a User from the list of users who favorited this album.
     *
     * @param User $user the user to remove
     *
     * @return static returns the instance of the current class
     */
    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeFavorite($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    /**
     * Add a Rating to the album.
     *
     * @param Rating $rating the rating to add
     *
     * @return static returns the instance of the current class
     */
    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setAlbum($this);
        }

        return $this;
    }

    /**
     * Remove a Rating form the album.
     *
     * @param Rating $rating the rating to remove
     *
     * @return static returns the instance of the current class
     */
    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getAlbum() === $this) {
                $rating->setAlbum(null);
            }
        }

        return $this;
    }

    /**
     * Getter for cover.
     *
     * @return Cover|null the cover of the album
     */
    public function getCover(): ?Cover
    {
        return $this->cover;
    }

    /**
     * Setter for Cover.
     *
     * @param Cover|null $cover the cover of the album
     *
     * @return static returns the instance of the current class
     */
    public function setCover(?Cover $cover): static
    {

        $this->cover = $cover;
        if ($cover && $cover->getAlbum() !== $this) {
            $cover->setAlbum($this);
        }

        $this->cover = $cover;

        return $this;
    }
}
