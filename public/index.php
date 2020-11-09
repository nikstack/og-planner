<?php

require_once 'config.php';
require_once 'bootstrap.php';
require_once BASEDIR . 'src/ogPlanner/model/IEntry.php';
require_once BASEDIR . 'src/ogPlanner/model/Entry.php';
require_once BASEDIR . 'src/ogPlanner/model/User.php';
require_once BASEDIR . 'src/ogPlanner/model/Timetable.php';

require_once BASEDIR . 'src/ogPlanner/dao/IUserRepo.php';
// require_once BASEDIR . 'src/ogPlanner/dao/SimpleUserRepo.php';

require_once BASEDIR . 'src/ogPlanner/utils/Util.php';
require_once BASEDIR . 'src/ogPlanner/utils/OGMailer.php';
require_once BASEDIR . 'src/ogPlanner/utils/AbstractScraper.php';
require_once BASEDIR . 'src/ogPlanner/utils/OGScraper.php';
require_once BASEDIR . 'src/ogPlanner/utils/TableScraper.php';

use Doctrine\ORM\EntityManager;
use ogPlanner\dao\ITimetableRepo;
use ogPlanner\dao\IUserCourseTimetableConnectorRepo;
use ogPlanner\dao\IUserRepo;
use ogPlanner\model\IEntry;
use ogPlanner\model\ITimetable;
use ogPlanner\model\IUserCourseTimetableConnector;
use ogPlanner\model\Timetable;
use ogPlanner\model\User;
use ogPlanner\model\UserCourseTimetableConnector;
use ogPlanner\utils\OGMailer;
use ogPlanner\utils\OGScraper;
use ogPlanner\utils\TableScraper;
use ogPlanner\utils\Util;


function withLogger($msg, $fun): void
{
    $code = $fun();
    Util::logToFile(sprintf($msg, $code));
    echo sprintf($msg, $code);
}

function main(): int
{
    $ogScraper = new OGScraper(PLANNER_URL);
    $ogScraperData = $ogScraper->scrape();

    if (!Util::updateFileContents($ogScraperData['plan_update'], LAST_UPDATE)) {
//        return 2;
    }

    // Get table containing entries from website
    $tableScraper = new TableScraper(PLANNER_URL);
    $table = $tableScraper->scrape();
    if ($table->isEmpty()) {
        return 3;
    }

    $map = Util::convertTableToMap($table); // map[course][entries]

    /** @var EntityManager $entityManager */
    /** @var IUserCourseTimetableConnectorRepo $connectorRepo */
    /** @var IUserRepo $userRepo */
    $entityManager = getEntityManager();
    $connectorRepo = $entityManager->getRepository(UserCourseTimetableConnector::class);
    $userRepo = $entityManager->getRepository(User::class);

    foreach ($map as $course => $entries) {
        if (count($entries) == 0) {
            continue;
        }
        $relevantEntries = [];

        $connectors = $connectorRepo->findByCourse($course);

        $emailUsers = [];
        /** @var IUserCourseTimetableConnector $connector */
        foreach ($connectors as $connector) {
            $timetableId = $connector->getTimetableId();

            if ($timetableId == 0) { // There is no timetable, user must be a student in Unterstufe or Mittelstufe
                $relevantEntries = $entries;
            } else { // There is a timetable, user must be a student in Oberstufe
                /** @var ITimetableRepo $timetableRepo */
                $timetableRepo = $entityManager->getRepository(Timetable::class);
                $dateParsed = date_parse($ogScraperData['plan_date']);
                $dateStr = "{$dateParsed['day']}.{$dateParsed['month']}.{$dateParsed['year']}";
                $dayOfWeek = date('N', strtotime($dateStr)) - 1;
                $dayTimetables = $timetableRepo->findByTimetableIdAndDay($timetableId, $dayOfWeek);       // Only get timetables with matching date

                if ($dayTimetables == null) {
                    continue;
                }

                /** @var ITimetable $dayTimetable */
                foreach ($dayTimetables as $dayTimetable) {
                    // Wie sehen hier $entries aus?
                    /** @var IEntry $entry */
                    foreach ($entries as $entry) {
                        if ($dayTimetable->getLesson() == $entry->getLesson() &&
                            $dayTimetable->getSubject() == $entry->getSubject()) {    // Vertretung!
                            $relevantEntries[] = $entry;
                        }
                    }
                }
            }

            $emailUsers[] = $userRepo->findById($connector->getUserId()); // Füge Schüler zur E-Mail hinzu!
        }

        if ($emailUsers == []) {
            continue;
        }

        /** @var User $user */
        foreach ($emailUsers as $user) { // Todo: Obersufenschüler müssen nicht zu allen Entries Vertretung haben! man muss die Entries noch filtern! Relevant entries
            if (OGMailer::sendEntryMail($user, $relevantEntries, $ogScraperData['plan_date'])) {
                Util::logToFile('Successfully sent E-Mail to #' . $user->getId() . ' - ' . $user->getName() .
                    ' with E-Mail ' . $user->getEmail() . ' for course ' . $course);
            } else {
                Util::logToFile('Could not send E-Mail to #' . $user->getId() . ' - ' . $user->getName() .
                    ' with E-Mail ' . $user->getEmail() . ' for course ' . $course);
            }
        }
    }

    return EXIT_SUCCESS;
}

withLogger('Executed with Code: %d', function (): int {
    return main();
});
