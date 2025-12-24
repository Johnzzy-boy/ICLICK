<?php include 'header.php'; ?>

<section class="gallery-section">
    <div class="container">
        <h2 class="section-title">Our Gallery</h2>
        
        <?php
        include 'db.php';
        
        // Get filter from URL if set
        $filter = isset($_GET['type']) ? $_GET['type'] : 'all';
        $whereClause = '';
        if ($filter === 'images') {
            $whereClause = "WHERE type = 'image'";
        } elseif ($filter === 'videos') {
            $whereClause = "WHERE type = 'video'";
        }
        
        $stmt = $pdo->query("SELECT * FROM gallery $whereClause ORDER BY created_at DESC");
        $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="gallery.php?type=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Media</a>
            <a href="gallery.php?type=images" class="filter-btn <?php echo $filter === 'images' ? 'active' : ''; ?>">Images</a>
            <a href="gallery.php?type=videos" class="filter-btn <?php echo $filter === 'videos' ? 'active' : ''; ?>">Videos</a>
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
            <?php foreach($galleryItems as $item): ?>
            <div class="gallery-item fade-in" data-src="assets/<?php echo $item['type'] === 'image' ? 'images/gallery/' : 'videos/'; ?><?php echo $item['file_path']; ?>" data-type="<?php echo $item['type']; ?>">
                <div class="gallery-card" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <?php if($item['type'] === 'image'): ?>
                        <img src="assets/images/gallery/<?php echo $item['file_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div style="position: relative; height: 200px; background: #000; display: flex; align-items: center; justify-content: center;">
                            <video style="width: 100%; height: 100%; object-fit: cover;" poster="assets/images/video-poster.jpg">
                                <source src="assets/videos/<?php echo $item['file_path']; ?>" type="video/mp4">
                            </video>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 3rem;">▶</div>
                        </div>
                    <?php endif; ?>
                    <div style="padding: 15px;">
                        <h3 style="color: var(--teal); margin-bottom: 10px;"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p style="color: var(--text-color); font-size: 0.9rem;"><?php echo date('F j, Y', strtotime($item['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($galleryItems)): ?>
            <div style="text-align: center; padding: 50px;">
                <h3 style="color: var(--teal);">No gallery items found</h3>
                <p>Check back later for updates to our gallery.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox">
    <span class="lightbox-close">&times;</span>
    <div class="lightbox-content"></div>
</div>

<?php include '../includes/footer.php'; ?>