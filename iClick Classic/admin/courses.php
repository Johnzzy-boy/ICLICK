<?php include 'includes/header.php'; ?>

<section class="courses-section">
    <div class="container">
        <h2 class="section-title">Our Shows & Courses</h2>
        
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="reality">Reality TV</button>
            <button class="filter-btn" data-filter="documentary">Documentaries</button>
            <button class="filter-btn" data-filter="educational">Educational</button>
            <button class="filter-btn" data-filter="production">Production Courses</button>
        </div>
        
        <?php
        include 'includes/db.php';
        $stmt = $pdo->query("SELECT * FROM courses");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="courses-grid">
            <?php foreach($courses as $course): ?>
            <div class="course-card" data-category="<?php echo htmlspecialchars($course['category']); ?>">
                <div class="course-image">
                    <img src="assets/images/<?php echo $course['image'] ?: 'default-course.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                </div>
                <div class="course-content">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <span class="course-category"><?php echo htmlspecialchars($course['category']); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>