<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: index.html');
  exit;
}
$user = $_SESSION['user']; // id, name, email
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LinkedIn Mini — Feed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="./styles.css" rel="stylesheet">
</head>
<body class="bg">
  <div class="shell">
    <div class="topbar">
      <div class="brand small">LinkedIn <span>Mini</span></div>
      <div class="userbox">
        <span class="muted">Hi,</span> <strong><?php echo htmlspecialchars($user['name']); ?></strong>
        <button class="btn ghost" onclick="logout()">Logout</button>
      </div>
    </div>

    <div class="card">
      <h2>Create a post</h2>
      <form id="postForm" class="grid gap">
        <textarea id="content" rows="3" placeholder="Share something..." required></textarea>
        <button class="btn">Post</button>
        <div id="post_error" class="error"></div>
      </form>
    </div>

    <div class="card" style="margin-top:16px">
      <h2>Public Feed</h2>
      <div id="feed" class="feed">Loading...</div>
    </div>
  </div>

  <script>
    const API = "";
    async function logout(){
      await fetch('${API}/logout.php', {method:'POST', credentials:'include'});
      window.location.href = 'index.html';
    }

    function escapeHTML(s){ return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    async function loadFeed(){
      const feed = document.getElementById('feed');
      feed.textContent = 'Loading...';
      try {
        const res = await fetch('${API}/get_posts.php', {credentials:'include'});
        const html = await res.text();
        feed.innerHTML = html || '<div class="post muted">No posts yet.</div>';
      } catch(e){
        feed.innerHTML = '<div class="post error">Failed to load feed.</div>';
      }
    }

    document.getElementById('postForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      document.getElementById('post_error').textContent = '';
      const content = document.getElementById('content').value.trim();
      if (!content) return;
      try {
        const res = await fetch('${API}/post.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          credentials:'include',
          body: JSON.stringify({content})
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data.error || 'Failed to post');
        document.getElementById('content').value = '';
        loadFeed();
      } catch(err){
        document.getElementById('post_error').textContent = err.message;
      }
    });

    loadFeed();
  </script>
</body>
</html>

