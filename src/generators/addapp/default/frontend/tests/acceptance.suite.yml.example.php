suite_namespace: <?=$generator->appname?>\tests\acceptance
actor: AcceptanceTester
modules:
    enabled:
        - WebDriver:
            url: http://localhost:8080
            browser: firefox
        - Yii2:
            part: init