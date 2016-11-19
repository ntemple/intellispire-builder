<?php
    ini_set('include_path', ini_get('include_path') . ":/usr/local/lib/php/core:/usr/local/lib/php/core/pear");
    require_once('simpletest/unit_tester.php');
    require_once('simpletest/reporter.php');

    # Library Test
    require_once('library.inc.php');

    class LibraryTestCase extends UnitTestCase {
        function FileTestCase() {
            $this->UnitTestCase('Library test');
        }

        function testFixLocation() {
          $location = fix_location('www.example.com');
          $this->assertTrue($location == 'http://www.example.com');

          $location = fix_location('http://www.example.com');
          $this->assertTrue($location == 'http://www.example.com');


          $location = fix_location('www.example.com?');
          $this->assertTrue($location == 'http://www.example.com?');

          $location = fix_location('http://www.example.com?a=b');
          $this->assertTrue($location == 'http://www.example.com?a=b');


          # Now check for our seperator
          $location = fix_location('www.example.com', true);
          $this->assertTrue($location == 'http://www.example.com?');

          $location = fix_location('http://www.example.com', true);
          $this->assertTrue($location == 'http://www.example.com?');


          $location = fix_location('www.example.com?', true);
print $location;
          $this->assertTrue($location == 'http://www.example.com?');

          $location = fix_location('http://www.example.com?a=b', true);
          $this->assertTrue($location == 'http://www.example.com?a=b&');

          

        }

    }
    
    $test = &new LibraryTestCase();
    $test->run(new HtmlReporter());
?>  

