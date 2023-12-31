<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220919145300_add_scheduled_jobs_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220919145300_add_scheduled_jobs';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_an_instance_if_not_present()
    {
        $this->deleteJobInstance('job_executions_purge');
        $this->assertNull($this->jobInstanceId('job_executions_purge'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertNotNull($this->jobInstanceId('job_executions_purge'));
    }

    public function test_it_does_not_adds_an_instance_if_present()
    {
        $jobInstanceId = $this->jobInstanceId('job_executions_purge');
        $this->assertNotNull($jobInstanceId);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertEquals($jobInstanceId, $this->jobInstanceId('job_executions_purge'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function deleteJobInstance(string $jobInstanceCode)
    {
        $this->connection->executeStatement(
            "DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code",
            [
                'job_instance_code' => $jobInstanceCode,
            ]
        );
    }

    private function jobInstanceId(string $jobCode): ?int
    {
        $sql = 'SELECT id FROM akeneo_batch_job_instance WHERE code = :jobCode';

        return $this->connection->executeQuery($sql, ['jobCode' => $jobCode])
                ->fetchFirstColumn()[0] ?? null;
    }
}
