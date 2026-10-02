<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Global Search Results Page View
 */
$query_esc = html_escape($query);
?>

<div class="space-y-6">

  <!-- Header Card -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-primary font-semibold text-sm mb-1">
          <span class="material-symbols-outlined text-[20px]">manage_search</span>
          <span>Global Application Search</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">
          <?php if (!empty($query)): ?>
            Search results for <span class="text-primary font-extrabold">"<?php echo $query_esc; ?>"</span>
          <?php else: ?>
            Search School Records
          <?php endif; ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          <?php if (!empty($query)): ?>
            Found <span class="font-semibold text-slate-700"><?php echo (int)$results['total']; ?></span> matching records across school database.
          <?php else: ?>
            Search across students, teachers, staff, classes, divisions, and subjects.
          <?php endif; ?>
        </p>
      </div>

      <!-- Search Box on page -->
      <form action="<?php echo site_url('search'); ?>" method="GET" class="w-full md:w-96">
        <input type="hidden" name="tab" value="<?php echo html_escape($tab); ?>">
        <div class="relative flex items-center">
          <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[20px]">search</span>
          <input 
            type="text" 
            name="q" 
            value="<?php echo $query_esc; ?>" 
            placeholder="Search name, admission #, code, phone..." 
            class="w-full pl-10 pr-24 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white text-sm text-slate-800 rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
            required
            minlength="2"
          />
          <button type="submit" class="absolute right-1.5 px-3 py-1.5 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg shadow-sm transition">
            Search
          </button>
        </div>
      </form>
    </div>

    <!-- Category Tabs -->
    <div class="flex flex-wrap items-center gap-2 mt-6 pt-5 border-t border-slate-100">
      <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=all'); ?>" 
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $tab === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'; ?>">
        <span class="material-symbols-outlined text-[16px]">grid_view</span>
        <span>All</span>
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] <?php echo $tab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
          <?php echo (int)$results['total']; ?>
        </span>
      </a>

      <?php if (!empty($permissions['students'])): ?>
        <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=students'); ?>" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $tab === 'students' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'; ?>">
          <span class="material-symbols-outlined text-[16px]">school</span>
          <span>Students</span>
          <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] <?php echo $tab === 'students' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
            <?php echo (int)$results['students']['count']; ?>
          </span>
        </a>
      <?php endif; ?>

      <?php if (!empty($permissions['staff'])): ?>
        <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=staff'); ?>" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $tab === 'staff' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'; ?>">
          <span class="material-symbols-outlined text-[16px]">badge</span>
          <span>Staff / Teachers</span>
          <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] <?php echo $tab === 'staff' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
            <?php echo (int)$results['staff']['count']; ?>
          </span>
        </a>
      <?php endif; ?>

      <?php if (!empty($permissions['classes'])): ?>
        <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=classes'); ?>" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $tab === 'classes' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'; ?>">
          <span class="material-symbols-outlined text-[16px]">meeting_room</span>
          <span>Classes &amp; Divisions</span>
          <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] <?php echo $tab === 'classes' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
            <?php echo (int)$results['classes']['count']; ?>
          </span>
        </a>
      <?php endif; ?>

      <?php if (!empty($permissions['subjects'])): ?>
        <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=subjects'); ?>" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition <?php echo $tab === 'subjects' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'; ?>">
          <span class="material-symbols-outlined text-[16px]">menu_book</span>
          <span>Subjects</span>
          <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] <?php echo $tab === 'subjects' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
            <?php echo (int)$results['subjects']['count']; ?>
          </span>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($query) || mb_strlen($query) < 2): ?>
    <!-- Initial / Short State -->
    <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm">
      <div class="w-16 h-16 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-[32px]">search</span>
      </div>
      <h3 class="text-lg font-bold text-slate-800">Search Across the Entire School</h3>
      <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
        Enter at least 2 characters to search across students, admission numbers, roll numbers, staff, classes, and subjects.
      </p>
      <div class="flex flex-wrap justify-center gap-2 text-xs text-slate-600">
        <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 font-mono">EDU2026132</span>
        <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 font-mono">Anandhu</span>
        <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 font-mono">TCH2026001</span>
        <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 font-mono">Grade 10</span>
        <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 font-mono">Mathematics</span>
      </div>
    </div>
  <?php elseif ($results['total'] === 0): ?>
    <!-- No Results State -->
    <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm">
      <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-[32px]">search_off</span>
      </div>
      <h3 class="text-lg font-bold text-slate-800">No results found for "<?php echo $query_esc; ?>"</h3>
      <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
        We couldn't find any matching school records. Please verify the spelling or try searching by admission number, roll number, or phone number.
      </p>
      <div class="inline-block text-left bg-slate-50 p-4 rounded-xl border border-slate-200/80 text-xs text-slate-600 max-w-md">
        <div class="font-semibold text-slate-800 mb-2 flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px] text-primary">lightbulb</span>
          <span>Helpful Search Tips:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-slate-500">
          <li>Check for typos or misspellings in the student/staff name.</li>
          <li>Search exact admission number like <span class="font-mono text-slate-700">EDU2026132</span>.</li>
          <li>Search employee code like <span class="font-mono text-slate-700">TCH2026001</span>.</li>
          <li>Search with phone digits directly without symbols (e.g. 9876543210).</li>
        </ul>
      </div>
    </div>
  <?php else: ?>

    <!-- Results Container -->
    <div class="space-y-6">

      <!-- Students Section -->
      <?php if (($tab === 'all' || $tab === 'students') && !empty($results['students']['items'])): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
              <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg material-symbols-outlined text-[20px]">school</span>
              <h2 class="text-base font-bold text-slate-800">Students</h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100/70 text-blue-700">
                <?php echo (int)$results['students']['count']; ?>
              </span>
            </div>
            <?php if ($tab === 'all' && $results['students']['count'] > count($results['students']['items'])): ?>
              <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=students'); ?>" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                <span>View all <?php echo (int)$results['students']['count']; ?> students</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </a>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($results['students']['items'] as $student): ?>
              <div class="p-4 rounded-xl border border-slate-200/70 hover:border-primary/40 hover:shadow-sm bg-slate-50/40 hover:bg-white transition flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                  <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-sm overflow-hidden">
                    <?php if (!empty($student['photo']) && file_exists(FCPATH . 'uploads/students/' . $student['photo'])): ?>
                      <img src="<?php echo base_url('uploads/students/' . $student['photo']); ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                      <span class="material-symbols-outlined text-[22px]">person</span>
                    <?php endif; ?>
                  </div>
                  <div>
                    <a href="<?php echo $student['url']; ?>" class="font-bold text-slate-900 group-hover:text-primary transition text-sm flex items-center gap-1.5">
                      <span><?php echo html_escape($student['title']); ?></span>
                      <?php if ($student['is_active_year']): ?>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">Active</span>
                      <?php endif; ?>
                    </a>
                    <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                      <span class="font-mono font-medium text-slate-700">Adm: <?php echo html_escape($student['admission_number']); ?></span>
                      <?php if (!empty($student['roll_number'])): ?>
                        <span>•</span>
                        <span>Roll: <strong class="text-slate-800"><?php echo html_escape($student['roll_number']); ?></strong></span>
                      <?php endif; ?>
                      <span>•</span>
                      <span class="text-slate-700 font-medium"><?php echo html_escape($student['class_name']); ?></span>
                    </div>
                    <?php if (!empty($student['guardian_name']) || !empty($student['phone'])): ?>
                      <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                        <?php if (!empty($student['guardian_name'])): ?>
                          <span>Guardian: <?php echo html_escape($student['guardian_name']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($student['phone'])): ?>
                          <span>•</span>
                          <span class="font-mono"><?php echo html_escape($student['phone']); ?></span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <a href="<?php echo $student['url']; ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 group-hover:bg-primary group-hover:border-primary group-hover:text-white transition flex-shrink-0">
                  Profile
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Staff Section -->
      <?php if (($tab === 'all' || $tab === 'staff') && !empty($results['staff']['items'])): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
              <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg material-symbols-outlined text-[20px]">badge</span>
              <h2 class="text-base font-bold text-slate-800">Staff / Teachers</h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100/70 text-purple-700">
                <?php echo (int)$results['staff']['count']; ?>
              </span>
            </div>
            <?php if ($tab === 'all' && $results['staff']['count'] > count($results['staff']['items'])): ?>
              <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=staff'); ?>" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                <span>View all <?php echo (int)$results['staff']['count']; ?> staff</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </a>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($results['staff']['items'] as $staff): ?>
              <div class="p-4 rounded-xl border border-slate-200/70 hover:border-primary/40 hover:shadow-sm bg-slate-50/40 hover:bg-white transition flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                  <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-700 font-bold flex items-center justify-center flex-shrink-0 text-sm overflow-hidden">
                    <span class="material-symbols-outlined text-[22px]">badge</span>
                  </div>
                  <div>
                    <a href="<?php echo $staff['url']; ?>" class="font-bold text-slate-900 group-hover:text-primary transition text-sm flex items-center gap-1.5">
                      <span><?php echo html_escape($staff['title']); ?></span>
                      <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-200 text-slate-700">
                        <?php echo html_escape($staff['role']); ?>
                      </span>
                    </a>
                    <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                      <span class="font-mono font-medium text-slate-700">Code: <?php echo html_escape($staff['employee_code']); ?></span>
                      
                    </div>
                    <?php if (!empty($staff['phone']) || !empty($staff['email'])): ?>
                      <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                        <?php if (!empty($staff['phone'])): ?>
                          <span class="font-mono"><?php echo html_escape($staff['phone']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($staff['email'])): ?>
                          <span>•</span>
                          <span><?php echo html_escape($staff['email']); ?></span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <a href="<?php echo $staff['url']; ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 group-hover:bg-primary group-hover:border-primary group-hover:text-white transition flex-shrink-0">
                  Profile
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Classes & Divisions Section -->
      <?php if (($tab === 'all' || $tab === 'classes') && !empty($results['classes']['items'])): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
              <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg material-symbols-outlined text-[20px]">meeting_room</span>
              <h2 class="text-base font-bold text-slate-800">Classes &amp; Divisions</h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100/70 text-emerald-700">
                <?php echo (int)$results['classes']['count']; ?>
              </span>
            </div>
            <?php if ($tab === 'all' && $results['classes']['count'] > count($results['classes']['items'])): ?>
              <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=classes'); ?>" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                <span>View all <?php echo (int)$results['classes']['count']; ?> classes</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </a>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($results['classes']['items'] as $class): ?>
              <div class="p-4 rounded-xl border border-slate-200/70 hover:border-primary/40 hover:shadow-sm bg-slate-50/40 hover:bg-white transition flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                  <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center flex-shrink-0 text-sm">
                    <span class="material-symbols-outlined text-[22px]">meeting_room</span>
                  </div>
                  <div>
                    <a href="<?php echo $class['url']; ?>" class="font-bold text-slate-900 group-hover:text-primary transition text-sm flex items-center gap-1.5">
                      <span><?php echo html_escape($class['title']); ?></span>
                      <?php if ($class['is_active_year']): ?>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">Active Year</span>
                      <?php endif; ?>
                    </a>
                    <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                      <span class="font-mono font-medium text-slate-700">Code: <?php echo html_escape($class['class_code'] ?: 'N/A'); ?></span>
                      <span>•</span>
                      <span>Divisions: <strong class="text-slate-800"><?php echo html_escape($class['divisions']); ?></strong></span>
                    </div>
                    <?php if (!empty($class['academic_year'])): ?>
                      <div class="text-[11px] text-slate-500 mt-1">
                        Academic Year: <?php echo html_escape($class['academic_year']); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <a href="<?php echo $class['url']; ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 group-hover:bg-primary group-hover:border-primary group-hover:text-white transition flex-shrink-0">
                  Manage
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Subjects Section -->
      <?php if (($tab === 'all' || $tab === 'subjects') && !empty($results['subjects']['items'])): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
              <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg material-symbols-outlined text-[20px]">menu_book</span>
              <h2 class="text-base font-bold text-slate-800">Subjects</h2>
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100/70 text-amber-700">
                <?php echo (int)$results['subjects']['count']; ?>
              </span>
            </div>
            <?php if ($tab === 'all' && $results['subjects']['count'] > count($results['subjects']['items'])): ?>
              <a href="<?php echo site_url('search?q=' . urlencode($query) . '&tab=subjects'); ?>" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                <span>View all <?php echo (int)$results['subjects']['count']; ?> subjects</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </a>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($results['subjects']['items'] as $subject): ?>
              <div class="p-4 rounded-xl border border-slate-200/70 hover:border-primary/40 hover:shadow-sm bg-slate-50/40 hover:bg-white transition flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                  <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 font-bold flex items-center justify-center flex-shrink-0 text-sm">
                    <span class="material-symbols-outlined text-[22px]">menu_book</span>
                  </div>
                  <div>
                    <a href="<?php echo $subject['url']; ?>" class="font-bold text-slate-900 group-hover:text-primary transition text-sm flex items-center gap-1.5">
                      <span><?php echo html_escape($subject['title']); ?></span>
                      <?php if (!empty($subject['subject_type'])): ?>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-200 text-slate-700">
                          <?php echo html_escape($subject['subject_type']); ?>
                        </span>
                      <?php endif; ?>
                    </a>
                    <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                      <span class="font-mono font-medium text-slate-700">Code: <?php echo html_escape($subject['subject_code']); ?></span>
                      <span>•</span>
                      <span>Class: <?php echo html_escape($subject['class_name']); ?></span>
                    </div>
                    <?php if (!empty($subject['academic_year'])): ?>
                      <div class="text-[11px] text-slate-500 mt-1">
                        Academic Year: <?php echo html_escape($subject['academic_year']); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <a href="<?php echo $subject['url']; ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 group-hover:bg-primary group-hover:border-primary group-hover:text-white transition flex-shrink-0">
                  View
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  <?php endif; ?>

</div>
