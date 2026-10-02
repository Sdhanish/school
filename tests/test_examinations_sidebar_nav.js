const fs = require('fs');
const path = require('path');

const appJsContent = fs.readFileSync(path.resolve(__dirname, '../assets/app.js'), 'utf8');

// Mock window and browser globals
const window = {
  APP_BASE_URL: 'http://localhost/schoolnew/',
  CURRENT_ACADEMIC_YEAR_ID: 1,
  CURRENT_ACADEMIC_YEAR: { year_name: '2026-2027' },
  AVAILABLE_ACADEMIC_YEARS: [{ academic_year_id: 1, year_name: '2026-2027' }],
  CAN_CHANGE_ACADEMIC_YEAR: true,
  CURRENT_USER: { first_name: 'Admin', last_name: 'User', role: 'admin' },
};
const School = {};
window.School = School;
const document = {
  body: { classList: { toggle: () => {}, contains: () => false } },
  addEventListener: () => {},
  querySelector: () => null,
  querySelectorAll: () => []
};

// Evaluate app.js in a VM context to test NAV, findActiveHierarchy, renderSidebar
const vm = require('vm');
const context = vm.createContext({ window, document, console, setTimeout, clearTimeout, School });
const exported = vm.runInContext(appJsContent + '; ({ NAV, PAGE_URLS, findActiveHierarchy, renderSidebar })', context);
const NAV = exported.NAV;
const PAGE_URLS = exported.PAGE_URLS;
const findActiveHierarchy = exported.findActiveHierarchy;
const renderSidebar = exported.renderSidebar;

let testsRun = 0;
let testsPassed = 0;

function assert(desc, condition) {
  testsRun++;
  if (condition) {
    testsPassed++;
    console.log(`  [PASS] ${desc}`);
  } else {
    console.error(`  [FAIL] ${desc}`);
  }
}

console.log('=======================================================');
console.log('1. Verify NAV Structure for Examination & Results');
console.log('=======================================================');

const examNav = NAV.find(item => item.key === 'examinations');
assert('Examination & Results module exists in NAV', !!examNav);

// Check that "Reports" (exam-reports) is NOT in examNav.groups
const hasReportsUnderExam = examNav.groups.some(g => 
  g.key === 'exam-reports' || (g.items && g.items.some(c => c.key === 'exam-reports'))
);
assert('"Reports" submenu is REMOVED from Examination & Results', hasReportsUnderExam === false);

// Check remaining submenus under Examination & Results
const expectedKeys = [
  'exam-dashboard',
  'exams',
  'grade-management',
  'exam-schedules',
  'marks-entry',
  'result-calculation',
  'report-cards',
  'exam-ranks',
  'progress-reports'
];

let allExpectedFound = true;
expectedKeys.forEach(k => {
  const found = examNav.groups.some(g => 
    g.key === k || (g.items && g.items.some(c => c.key === k))
  );
  if (!found) {
    allExpectedFound = false;
    console.error(`  Missing expected key: ${k}`);
  }
});
assert('All other expected Examination submenus are preserved', allExpectedFound);

console.log('\n=======================================================');
console.log('2. Verify findActiveHierarchy Behavior');
console.log('=======================================================');

// 1. Direct navigation to exam-reports should keep examinations module active
const hReports = findActiveHierarchy('exam-reports');
assert('exam-reports maps to module examinations', hReports.moduleKey === 'examinations');

// 2. Marks Entry should open Marks & Results subgroup
const hMarks = findActiveHierarchy('marks-entry');
assert('marks-entry maps to module examinations', hMarks.moduleKey === 'examinations');
assert('marks-entry groupLabel is Marks & Results', hMarks.groupLabel === 'Marks & Results');

// 3. Result Calculation should open Marks & Results subgroup
const hCalc = findActiveHierarchy('result-calculation');
assert('result-calculation maps to module examinations', hCalc.moduleKey === 'examinations');
assert('result-calculation groupLabel is Marks & Results', hCalc.groupLabel === 'Marks & Results');

// 4. Report Cards should be in examinations
const hCards = findActiveHierarchy('report-cards');
assert('report-cards maps to module examinations', hCards.moduleKey === 'examinations');

// 5. Rank / Position should be in examinations
const hRanks = findActiveHierarchy('exam-ranks');
assert('exam-ranks maps to module examinations', hRanks.moduleKey === 'examinations');

// 6. Progress Reports should be in examinations
const hProgress = findActiveHierarchy('progress-reports');
assert('progress-reports maps to module examinations', hProgress.moduleKey === 'examinations');

console.log('\n=======================================================');
console.log('3. Verify Rendered Sidebar HTML');
console.log('=======================================================');

// Render sidebar when on exam-ranks page
const htmlRanks = renderSidebar('exam-ranks');
assert('Sidebar HTML contains Report Cards link', htmlRanks.includes('Report Cards'));
assert('Sidebar HTML contains Rank / Position link', htmlRanks.includes('Rank / Position'));
assert('Sidebar HTML contains Progress Reports link', htmlRanks.includes('Progress Reports'));
assert('Sidebar HTML contains Marks & Results subgroup', htmlRanks.includes('Marks &amp; Results') || htmlRanks.includes('Marks & Results'));

// Verify that within the examination nav-group, "Reports" does NOT appear as a menu item
const examGroupHtml = htmlRanks.split('data-module-key="examinations"')[1].split('data-module-key="')[0];
// It should not contain a link to examinations/reports
assert('Examination & Results group HTML does NOT contain link to examinations/reports', !examGroupHtml.includes('href="http://localhost/schoolnew/examinations/reports"'));

console.log('\n=======================================================');
console.log(`RESULTS: ${testsPassed} / ${testsRun} tests passed.`);
console.log('=======================================================');

if (testsPassed === testsRun) {
  console.log('SUCCESS: ALL TESTS PASSED!\n');
  process.exit(0);
} else {
  console.error(`FAILURE: ${testsRun - testsPassed} test(s) failed.\n`);
  process.exit(1);
}
