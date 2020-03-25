<?php
declare(strict_types = 1);

namespace web\Presenters;


use Nette\Application\UI\Presenter;
use web\Models\Emails;

class HomepagePresenter extends Presenter
{
    /**
     * @var Emails
     */
    private $emails;

    public function __construct(Emails $emails)
    {
        parent::__construct();
        $this->emails = $emails;
    }

    protected function startup()
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn() === false) {
            $this->redirect('Login:');
        }
    }

    public function actionDefault(): void
    {
        $count = $this->emails->getCount();
        $countToday = $this->emails->getCountInterval(date('Y-m-d'), date('Y-m-d'));
        $countYesterday = $this->emails->getCountInterval(date('Y-m-d'), date('Y-m-d'));
        $lastSevenDays = $this->emails->getCountInterval(date('Y-m-d', strtotime('- 6 day')), date('Y-m-d'));
        $lastThirtyDays = $this->emails->getCountInterval(date('Y-m-d', strtotime('- 30 day')), date('Y-m-d'));

        $formatter = static function (float $number) {
            return number_format($number, 0, ',', ' ');
        };

        $stats = [
            'Celkově' => $formatter((float) $count),
            'Dnes' => $formatter((float) $countToday),
            'Včera' => $formatter((float) $countYesterday),
            'Za posledních 7 dnů' => $formatter((float) $lastSevenDays),
            'Za posledních 30 dnů' => $formatter((float) $lastThirtyDays)
        ];
        $this->template->days = $this->emails->getCountByDays(30);
        $this->template->stats = $stats;

    }
}
