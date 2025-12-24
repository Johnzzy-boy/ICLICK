<?php include 'includes/header.php'; ?>
<?php
/*
  © 2025 B-TECH
  All code written and maintained by B-TECH.
*/
?>
<section class="image">
    <img src="Emma.JPG" align ="Right"></img>
 </section>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Creating Exceptional Television Content</h1>
        <p>At iClick Classic, we believe every brand has a story worth telling. For over a decade, we’ve been crafting commercials, documentaries, and digital content that don’t just look beautiful but move people and drive result.</p>
        <div class="hero-buttons">
            <a href="courses.php" class="btn">Explore Our Shows</a>
            <a href="join.php" class="btn btn-teal">Join Our Team</a>
        </div>
    </div>
</section>

<!-- Featured Courses/Shows Section -->
<section class="courses-section">
    <div class="container">
        <h2 class="section-title">Featured Shows & Courses</h2>
        
        <?php
        // Fetch featured courses from database
        include 'includes/db.php';
        $stmt = $pdo->query("SELECT * FROM courses LIMIT 3");
        $featuredCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="courses-grid">
            <?php foreach($featuredCourses as $course): ?>
            <div class="course-card fade-in">
                <div class="course-image">
                    <img src="assets/images/<?php echo $course['image'] ?: 'default-course.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                </div>
                <div class="course-content">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                    <a href="courses.php" class="btn btn-teal">Learn More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="courses.php" class="btn">View All Shows & Courses</a>
        </div>
    </div>
</section>

<!-- Gallery Preview Section -->
<section class="gallery-section">
    <div class="container">
        <h2 class="section-title">Our Work</h2>
        
        <?php
        // Fetch gallery items from database
        $stmt = $pdo->query("SELECT * FROM gallery LIMIT 6");
        $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="carousel">
            <div class="carousel-track">
                <?php foreach($galleryItems as $item): ?>
                <div class="carousel-slide">
                    <?php if($item['type'] == 'image'): ?>
                        <img src="assets/images/gallery/<?php echo $item['file_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php else: ?>
                        <video controls>
                            <source src="assets/videos/<?php echo $item['file_path']; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                    <div class="carousel-caption">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn prev">&#10094;</button>
            <button class="carousel-btn next">&#10095;</button>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="gallery.php" class="btn">View Full Gallery</a>
        </div>
    </div>
</section>

<!-- Clients Preview Section -->
<section class="clients-section" style="background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title">Our Partners</h2>
        
        <?php
        // Fetch clients from database
        $stmt = $pdo->query("SELECT * FROM clients LIMIT 4");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="clients-grid">
            <?php foreach($clients as $client): ?>
            <div class="client-card fade-in">
                <div class="client-logo">
                    <img src="assets/images/clients/<?php echo $client['logo'] ?: 'default-client.png'; ?>" alt="<?php echo htmlspecialchars($client['name']); ?>">
                </div>
                <h3><?php echo htmlspecialchars($client['name']); ?></h3>
                <?php if($client['testimonial']): ?>
                    <p class="client-testimonial">"<?php echo htmlspecialchars($client['testimonial']); ?>"</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="clients.php" class="btn">View All Clients</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>