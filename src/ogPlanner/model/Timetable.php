<?php


namespace ogPlanner\model;

/**
 * @ORM\Entity(repositoryClass="TimetableRepo")
 * @ORM\Table(name="timetable")
 */
class Timetable implements ITimetable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $id;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $timetableId;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $day;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $lesson;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $subject;

    /**
     * Timetable constructor.
     * @param int $id
     * @param int $timetableId
     * @param int $day
     * @param int $lesson
     * @param string $subject
     */
    public function __construct(int $id, int $timetableId, int $day, int $lesson, string $subject)
    {
        $this->id = $id;
        $this->timetableId = $timetableId;
        $this->day = $day;
        $this->lesson = $lesson;
        $this->subject = $subject;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTimetableId(): int
    {
        return $this->timetableId;
    }

    /**
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @return int
     */
    public function getLesson(): int
    {
        return $this->lesson;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }
}