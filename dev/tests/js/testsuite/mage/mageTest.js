/**
 * {license_notice}
 *
 * @category    mage.event
 * @package     test
 * @copyright   {copyright}
 * @license     {license_link}
 */
MageTest = TestCase('MageTest');
MageTest.prototype.testTrigger = function () {
  mage.event.observe('test.event', function (e, o) {
    o.status = true;
  });
  var obj = {status: false};
  assertEquals(false, obj.status);
  mage.event.trigger('test.event', obj);
  assertEquals(true, obj.status);
};
MageTest.prototype.testLoad = function () {
  assertEquals(1, mage.load.js("test1"));
  assertEquals(1, mage.load.jsSync("test2"));
  assertEquals(1, mage.load.js("test1"));
  assertEquals(1, mage.load.jsSync("test2"));
};
MageTest.prototype.testLoadLanguage = function () {
  assertEquals(1, mage.load.language('en'));
  assertEquals(1, mage.load.language());
  assertEquals(5, mage.load.language('de'));
  var cookieName = 'language';
  $.cookie(cookieName, 'fr');
  assertEquals(7, mage.load.language());
  $.cookie(cookieName, null);
};


