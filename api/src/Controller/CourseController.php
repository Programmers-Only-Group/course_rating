<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Services\Course\Builder;
use App\Services\Course\Updater;
use App\Services\Validation\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/courses", name="api_courses_")
 */
class CourseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CourseRepository $courseRepository;
    private Builder $courseBuilder;
    private Updater $courseUpdater;

    /**
     * CourseController constructor.
     *
     * @param CourseRepository $courseRepository
     * @param Builder $courseBuilder
     * @param Updater $courseUpdater
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CourseRepository $courseRepository,
        Builder $courseBuilder,
        Updater $courseUpdater,
        EntityManagerInterface $entityManager
    ) {
        $this->courseRepository = $courseRepository;
        $this->courseBuilder = $courseBuilder;
        $this->courseUpdater = $courseUpdater;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="list", methods={"GET"})
     *
     * @return Response
     */
    public function coursesList(): Response
    {
        $courses = $this->courseRepository->findAll();

        return $this->json(
            array_map(fn(Course $course) => $course->getData(), $courses)
        );
    }

    /**
     * @Route("", name="add", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ValidationException
     */
    public function addCourse(Request $request): Response
    {
        $data = $this->courseBuilder->getDefaultData();

        foreach ($data as $key => $value) {
            $data[$key] = $request->get($key, $value);
        }

        $course = $this->courseBuilder
            ->setData($data)
            ->createCourseWithUserPermissions();

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $this->json($course->getData(), Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="edit", methods={"PUT"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function editCourse(Request $request, int $id)
    {
        $course = $this->courseRepository->find($id);

        $this->courseUpdater
            ->setCourse($course)
            ->setData($request->request->all())
            ->update();

        $this->entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-technologies", name="add_course_technolog", methods={"POST"})
     */
    public function addCourseTechnology(Request $request, int $id): Response
    {
        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-technologies/{courseTechnologyId}", name="remove_course_technology", methods={"DELETE"})
     */
    public function removeCourseTechnology(Request $request, int $id, int $courseTechnologyId): Response
    {
        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-characteristics", name="add_course_characteristic", methods={"POST"})
     */
    public function addCourseCharacteristic(Request $request, int $id): Response
    {
        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-characteristics/{courseCharacteristicId}", name="remove_course_characteristic", methods={"DELETE"})
     */
    public function removeCourseCharacteristic(Request $request, int $id, int $courseCharacteristicId): Response
    {
        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-translations", name="add_course_translation", methods={"POST"})
     */
    public function addCourseTranslation(Request $request, int $id): Response
    {
        return $this->json(true);
    }

    /**
     * @Route("/{id}/course-translations/{courseTranslationId}", name="remove_course_translation", methods={"DELETE"})
     */
    public function removeCourseTranslation(Request $request, int $id, int $courseTranslationId): Response
    {
        return $this->json(true);
    }
}