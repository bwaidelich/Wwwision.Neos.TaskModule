<?php
declare(strict_types=1);
namespace Wwwision\Neos\TaskModule\Controller;

use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Runner\TaskRunner;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Domain\Task\TaskExecutionHistory;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Booting\Scripts;
use Neos\Flow\Mvc\Controller\ActionController;

final class TaskController extends ActionController
{
    /**
     * @Flow\InjectConfiguration(package="Neos.Flow")
     * @var array
     */
    protected array $flowSettings;

    private TaskCollectionFactory $taskCollectionFactory;
    private Scheduler $scheduler;
    private TaskExecutionRepository $taskExecutionRepository;

    public function __construct(TaskCollectionFactory $taskCollectionFactory, Scheduler $scheduler, TaskExecutionRepository $taskExecutionRepository, TaskRunner $taskRunner, TaskExecutionHistory $taskExecutionHistory)
    {
        $this->taskCollectionFactory = $taskCollectionFactory;
        $this->scheduler = $scheduler;
        $this->taskExecutionRepository = $taskExecutionRepository;
    }

    public function indexAction(): void
    {
        $this->scheduler->scheduleTasks();
        $this->view->assign('taskDescriptors', array_map(fn (Task $task) => $this->taskDescriptor($task), $this->taskCollectionFactory->buildTasksFromConfiguration()->toArray()));
    }

    public function showAction(string $taskId): void
    {
        /** @var Task|null $task */
        $task = $this->taskCollectionFactory->buildTasksFromConfiguration()->get($taskId);
        if ($task === null) {
            $this->addFlashMessage('Failed to load task with id "%s"', 'Error', Message::SEVERITY_ERROR, [$taskId]);
            $this->redirect('index');
            return;
        }
        $this->view->assignMultiple($this->taskDescriptor($task));
        $this->view->assign('taskExecutions', $this->taskExecutionRepository->findLatestExecution($task));
    }

    public function runAllAction(): void
    {
        Scripts::executeCommandAsync('flowpack.task:task:run', $this->flowSettings);
        $this->addFlashMessage('Scheduled all configured tasks for execution', 'Tasks scheduled', Message::SEVERITY_NOTICE);
        $this->redirect('index');
    }

    public function runSingleAction(string $taskId): void
    {
        Scripts::executeCommandAsync('flowpack.task:task:runsingle', $this->flowSettings, ['taskIdentifier' => $taskId]);
        $this->addFlashMessage('Scheduled task "%s" for execution', 'Task scheduled', Message::SEVERITY_NOTICE, [$taskId]);
        $this->redirect('show', null, null, ['taskId' => $taskId]);
    }

    private function taskDescriptor(Task $task): array
    {
        $nextExecution = $this->taskExecutionRepository->findNextScheduled((new \DateTime())->add(new \DateInterval('P10Y')), [], $task);
        return [
            'task' => $task,
            'latestExecution' => $this->taskExecutionRepository->findLatestExecution($task, 1)->getFirst(),
            'nextExecution' => $nextExecution,
            'delayed' => $nextExecution !== null && $nextExecution->getScheduleTime() !== null && $nextExecution->getScheduleTime() < new \DateTimeImmutable(),
        ];
    }
}
