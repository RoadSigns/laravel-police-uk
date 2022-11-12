<?php

declare(strict_types=1);

namespace RoadSigns\LaravelPoliceUK\Domain\Neighbourhoods;

use Carbon\Carbon;

final class Priority
{
    private string $issue;

    private Carbon $issueDate;

    private string $action;

    private ?Carbon $actionDate;

    public function __construct(
        string $issue,
        Carbon $issueDate,
        string $action,
        ?Carbon $actionDate,
    ) {
        $this->issueDate = $issueDate;
        $this->action = $action;
        $this->actionDate = $actionDate;
        $this->issue = $issue;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function actionDate(): ?Carbon
    {
        return $this->actionDate;
    }

    public function issue(): string
    {
        return $this->issue;
    }

    public function issueDate(): Carbon
    {
        return $this->issueDate;
    }
}
