<?php include 'includes/header.php'; ?>
<?php
/*
  © 2025 B-TECH
  All code written and maintained by B-TECH.
*/
?>


<section class="clients-section">
    <div class="container">
        <h2 class="section-title">Our Valued Clients & Partners</h2>
        
        <?php
        include 'includes/db.php';
        $stmt = $pdo->query("SELECT * FROM clients ORDER BY name");
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
                <div class="client-details" style="margin-top: 15px; font-size: 0.9rem; color: var(--text-color);">
                    Partner since <?php echo date('Y', strtotime($client['created_at'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($clients)): ?>
            <div style="text-align: center; padding: 50px;">
                <h3 style="color: var(--teal);">No clients to display</h3>
                <p>Our client portfolio will be updated soon.</p>
            </div>
        <?php endif; ?>
        
        <!-- Testimonials Section -->
        <div style="margin-top: 80px;">
            <h2 class="section-title">Client Testimonials</h2>
            
            <?php
            $testimonialClients = array_filter($clients, function($client) {
                return !empty($client['testimonial']);
            });
            
            if (!empty($testimonialClients)): 
            ?>
            <div class="testimonials-carousel" style="max-width: 800px; margin: 0 auto;">
                <?php foreach($testimonialClients as $client): ?>
                <div class="testimonial-item" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 20px 0; text-align: center;">
                    <div class="testimonial-content" style="font-size: 1.1rem; font-style: italic; color: var(--text-color); margin-bottom: 20px;">
                        "<?php echo htmlspecialchars($client['testimonial']); ?>"
                    </div>
                    <div class="testimonial-author" style="font-weight: 600; color: var(--teal);">
                        — <?php echo htmlspecialchars($client['name']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 30px;">
                <p>Testimonials will be added soon.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Partnership Inquiry Section -->
<section class="form-section" style="background-color: var(--teal-light);">
    <div class="container">
        <div style="text-align: center; color: white;">
            <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Interested in Partnering With Us?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 30px;">Let's create amazing television content together.</p>
            <a href="contact.php" class="btn" style="background: var(--orange);">Get In Touch</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>