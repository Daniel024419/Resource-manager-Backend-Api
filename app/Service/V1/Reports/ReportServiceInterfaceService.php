<?php

namespace App\Service\V1\Reports;


interface ReportServiceInterfaceService
{

    /**
     * Retrieves basic reports for the user.
     * These reports include utilization and contribution metrics.
     * Utilization metrics provide insights into the user's time management,
     * while contribution metrics detail the user's involvement in projects.
     *
     * @return array An array containing utilization and contribution data for the user.
     */

    public function basicUser(): array;


    /**
     * Retrieves clients reports .
     *
     * @return array An array reports on clients.
     */
    public function clients(): array;

    /**
     * Retrieves projects reports .
     *
     * @return array An array reports on projects.
     */
    public function projects(): array;

    /**
     * Retrieves utilization reports .
     *
     * @return array An array reports on clients.
     */
    public function utilization(): array;

    /**
     * Retrieves time off reports reports.
     *
     * @return array An array reports on clients.
     */
    public function timeOff(): array;
}