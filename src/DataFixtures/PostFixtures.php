<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [];
        foreach (['php', 'symfony', 'concurrency'] as $name) {
            $tags[$name] = (new Tag())->setName($name);
            $manager->persist($tags[$name]);
        }

        $post = (new Post())
            ->setTitle('Stop hand-rolling locks: concurrency in Symfony done right')
            ->setShortTitle('Stop hand-rolling locks')
            ->setShortDescription('A lock file looks fine in code review and falls apart under load. Here is why, and how the Symfony Lock component fixes it in a dozen lines.')
            ->setText(self::ARTICLE)
            ->setPublished(true)
            ->setCreatedAt(new \DateTimeImmutable('2026-09-28 10:00'));

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        $manager->persist($post);
        $manager->flush();
    }

    private const string ARTICLE = <<<'MD'
        Every backend developer eventually writes this code. A cron job runs every minute, the job sometimes takes longer than a minute, and suddenly **two copies are sending the same invoices**. The quick fix is a lock file. It passes review, it works on your laptop, and it is wrong.

        Let's see why, and what to use instead.

        ## The naive lock

        Here is the version I have seen in at least three production codebases:

        ```php
        final class SendInvoicesCommand
        {
            public function __invoke(): int
            {
                $lockFile = sys_get_temp_dir().'/send-invoices.lock';

                if (file_exists($lockFile)) {
                    return Command::SUCCESS; // someone else is running
                }

                touch($lockFile); // ⚠️ the race window is right here
                try {
                    $this->invoiceSender->sendPending();
                } finally {
                    unlink($lockFile);
                }

                return Command::SUCCESS;
            }
        }
        ```

        It has three separate problems:

        1. **Check-then-act is not atomic.** Two processes can both see "no file" and both call `touch()`.
        2. **A crash leaves the file behind.** `finally` doesn't run on `SIGKILL` or an OOM kill, so the job never runs again until someone deletes the file by hand.
        3. **It only works on one machine.** Scale to two servers and each one has its own `/tmp`.

        > A lock that is not atomic is not a lock. It is a suggestion.

        ## The Symfony way

        The Lock component gives you atomic acquisition, automatic expiry and pluggable storage. Install it first:

        ```bash
        composer require symfony/lock
        ```

        Pick a store in `.env`. Since this blog already runs PostgreSQL, advisory locks are free:

        ```ini
        LOCK_DSN=postgresql+advisory://root:root@database:5432/my_blog
        ```

        And the command becomes:

        ```php
        #[AsCommand(name: 'app:send-invoices')]
        final class SendInvoicesCommand
        {
            public function __construct(
                private readonly LockFactory $lockFactory,
                private readonly InvoiceSender $invoiceSender,
            ) {
            }

            public function __invoke(): int
            {
                $lock = $this->lockFactory->createLock('send-invoices', ttl: 300);

                if (!$lock->acquire()) {
                    return Command::SUCCESS;
                }

                try {
                    $this->invoiceSender->sendPending();
                } finally {
                    $lock->release();
                }

                return Command::SUCCESS;
            }
        }
        ```

        The `ttl` is the safety net for problem #2: if the process dies, the lock expires on its own instead of blocking the job forever.

        ## Choosing a store

        | Store | Shared between servers | Survives restart | Good for |
        |---|---|---|---|
        | `flock` | No | No | Single-server cron jobs |
        | `semaphore` | No | No | Same as above, no filesystem |
        | `redis` | Yes | Depends on config | High-frequency, short locks |
        | `postgresql+advisory` | Yes | No (released on disconnect) | You already have Postgres |

        ## Testing it

        A lock you have never seen fail is a lock you don't trust. The cheapest test is to hold the lock yourself and check that the command backs off:

        ```php
        public function testSkipsWhenAnotherRunHoldsTheLock(): void
        {
            $lock = self::getContainer()->get(LockFactory::class)->createLock('send-invoices');
            self::assertTrue($lock->acquire());

            $tester = new CommandTester((new Application(self::$kernel))->find('app:send-invoices'));
            $tester->execute([]);

            self::assertSame(0, $this->invoiceSender->sentCount());
        }
        ```

        ## Checklist

        - [x] Acquisition is atomic
        - [x] The lock expires if the process dies
        - [x] Works across several servers
        - [ ] You deleted the old `*.lock` files from `/tmp`

        ---

        Concurrency bugs are the worst kind: rare, expensive, and impossible to reproduce on demand. The fix here is a single `composer require` and a dozen lines of code, so there's no reason to keep the lock file around.
        MD;
}
