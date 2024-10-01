document.addEventListener('DOMContentLoaded', function() {
  const taskList = document.getElementById('taskList');
  if (taskList) {
    new Sortable(taskList, {
      animation: 150,
      onEnd: function() {
        const taskOrder = Array.from(taskList.children).map(item => item.dataset.taskId);
        fetch(`/goals/${goalId}/tasks/reorder`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            taskOrder: taskOrder
          })
        });
      }
    });
  }

  // タスク編集
  document.querySelectorAll('.edit-task').forEach(button => {
    button.addEventListener('click', function() {
      const taskRow = this.closest('.task-item');
      const nameDisplay = taskRow.querySelector('.task-name-display');
      const nameInput = taskRow.querySelector('.task-name-input');
      const timeDisplay = taskRow.querySelector('.task-time-display');
      const timeInput = taskRow.querySelector('.task-time-input');
      const priorityDisplay = taskRow.querySelector('.task-priority-display');
      const prioritySelect = taskRow.querySelector('.task-priority-select');

      [nameDisplay, timeDisplay, priorityDisplay].forEach(el => el.classList.add('hidden'));
      [nameInput, timeInput, prioritySelect].forEach(el => el.classList.remove('hidden'));

      this.classList.add('hidden');
      taskRow.querySelector('.update-task').classList.remove('hidden');
    });
  });

  // タスク更新
  document.querySelectorAll('.update-task').forEach(button => {
    button.addEventListener('click', function() {
      const taskRow = this.closest('.task-item');
      const taskId = taskRow.dataset.taskId;
      const nameInput = taskRow.querySelector('.task-name-input');
      const timeInput = taskRow.querySelector('.task-time-input');
      const prioritySelect = taskRow.querySelector('.task-priority-select');

      fetch(`/goals/${goalId}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          name: nameInput.value,
          estimated_time: timeInput.value,
          priority: prioritySelect.value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const nameDisplay = taskRow.querySelector('.task-name-display');
          const timeDisplay = taskRow.querySelector('.task-time-display');
          const priorityDisplay = taskRow.querySelector('.task-priority-display');

          nameDisplay.textContent = nameInput.value;
          timeDisplay.textContent = timeInput.value;
          priorityDisplay.textContent = prioritySelect.options[prioritySelect.selectedIndex].text;

          [nameDisplay, timeDisplay, priorityDisplay].forEach(el => el.classList.remove('hidden'));
          [nameInput, timeInput, prioritySelect].forEach(el => el.classList.add('hidden'));

          this.classList.add('hidden');
          taskRow.querySelector('.edit-task').classList.remove('hidden');

          alert('タスクが更新されました');
        }
      });
    });
  });

  // タスク削除
  document.querySelectorAll('.delete-task').forEach(button => {
    button.addEventListener('click', function() {
      if (confirm('このタスクを削除してもよろしいですか？')) {
        const taskRow = this.closest('.task-item');
        const taskId = taskRow.dataset.taskId;

        fetch(`/goals/${goalId}/tasks/${taskId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            taskRow.remove();
          }
        });
      }
    });
  });
});