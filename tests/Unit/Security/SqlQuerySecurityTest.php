<?php

namespace Tests\Unit\Security;

use Tests\TestCase;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;
use NovaFlow\Core\Database\Grammar\PostgresGrammar;
use NovaFlow\Core\Database\Grammar\SqliteGrammar;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\RawExpression;
use InvalidArgumentException;

class SqlQuerySecurityTest extends TestCase
{
    private function createMockDb(): DatabaseInterface
    {
        return $this->createMock(DatabaseInterface::class);
    }

    public function testOperatorWhitelistAcceptsValidOperators(): void
    {
        $grammar = new MySqlGrammar();
        $validOperators = [
            '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
            'LIKE', 'LIKE BINARY', 'NOT LIKE', 'ILIKE',
            '&', '|', '^', '<<', '>>',
            'RLIKE', 'REGEXP', 'NOT REGEXP',
            '~', '~*', '!~', '!~*', 'SIMILAR TO',
            'NOT SIMILAR TO', 'NOT ILIKE', 'SOUNDS LIKE'
        ];

        foreach ($validOperators as $op) {
            $this->assertEquals(strtoupper($op), $grammar->validateOperator($op));
            $this->assertEquals(strtoupper($op), $grammar->validateOperator(strtolower($op)));
        }
    }

    public function testOperatorValidationRejectsMaliciousOperators(): void
    {
        $grammar = new MySqlGrammar();
        $injections = [
            '=', // valid
            '; DROP TABLE users; --',
            'UNION SELECT',
            'OR 1=1',
            '--',
            '/*',
            'SLEEP(5)',
            '||',
            'AND',
            'OR'
        ];

        foreach (array_slice($injections, 1) as $malicious) {
            try {
                $grammar->validateOperator($malicious);
                $this->fail("Expected InvalidArgumentException for operator [$malicious]");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Unsupported SQL operator', $e->getMessage());
            }
        }
    }

    public function testWhereCompilationEnforcesOperatorValidation(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('users');

        $this->expectException(InvalidArgumentException::class);
        $builder->where('status', 'UNION SELECT 1--', 'active')->toSql();
    }

    public function testWhereColumnEnforcesOperatorValidation(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('orders');

        $this->expectException(InvalidArgumentException::class);
        $builder->whereColumn('orders.user_id', '; DROP TABLE users; --', 'users.id')->toSql();
    }

    public function testWhereDateEnforcesOperatorValidation(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('invoices');

        $this->expectException(InvalidArgumentException::class);
        $builder->whereDate('created_at', 'MALICIOUS_OP', '2026-01-01')->toSql();
    }

    public function testWhereDateFuncEnforcesOperatorValidation(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('invoices');

        $this->expectException(InvalidArgumentException::class);
        $builder->whereYear('created_at', 'INJECTION_OP', 2026)->toSql();
    }

    public function testOrderBySanitizesMaliciousColumnStrings(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('users');

        // Attempt SQL injection in orderBy column
        $builder->orderBy('id; DROP TABLE users; --', 'ASC');
        $sql = $builder->toSql();

        $this->assertStringNotContainsString(';', $sql);
        $this->assertStringNotContainsString('--', $sql);
        $this->assertStringContainsString('ORDER BY idDROPTABLEusers ASC', $sql);
    }

    public function testOrderByAllowsValidColumnsAndTableAliases(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('users');

        $builder->orderBy('users.created_at', 'DESC');
        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY users.created_at DESC', $sql);
    }

    public function testOrderByNormalizesInvalidDirection(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('users');

        $builder->orderBy('id', 'INVALID_DIRECTION; --');
        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY id ASC', $sql);
    }

    public function testOrderByPreservesRawExpressionWithoutMangling(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('tasks');

        $rawExpr = new RawExpression('FIELD(status, "urgent", "normal", "low") ASC');
        $builder->orderBy($rawExpr);
        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY FIELD(status, "urgent", "normal", "low") ASC', $sql);
    }

    public function testOrderByRawSupportsCustomSql(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('products');

        $builder->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END, price DESC');
        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY CASE WHEN stock > 0 THEN 0 ELSE 1 END, price DESC', $sql);
    }

    public function testOrderByWorksWithPaginationAndChunking(): void
    {
        $db = $this->createMockDb();
        $builder = (new QueryBuilder($db))->table('users');

        $builder->orderBy('id', 'ASC')->forPage(2, 25);
        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY id ASC', $sql);
        $this->assertStringContainsString('LIMIT 25 OFFSET 25', $sql);
    }
}
