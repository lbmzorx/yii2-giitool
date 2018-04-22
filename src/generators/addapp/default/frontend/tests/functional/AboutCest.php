<?php echo "<?php\n"?>
namespace <?=$generator->appname?>\tests\functional;

use <?=$generator->appname?>\tests\FunctionalTester;

class AboutCest
{
    public function checkAbout(FunctionalTester $I)
    {
        $I->amOnRoute('site/about');
        $I->see('About', 'h1');
    }
}
