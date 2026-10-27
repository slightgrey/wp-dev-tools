<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap smwm-wrap">

    <h1 class="smwm-heading"><?php esc_html_e( 'Send Mail', 'admin-tools' ); ?></h1>
    <p class="smwm-subheading"><?php esc_html_e( 'Compose and send an email using WordPress wp_mail().', 'admin-tools' ); ?></p>

    <?php if ( isset( $_GET['smwm_sent'] ) && '1' === $_GET['smwm_sent'] ) : ?>
        <div class="smwm-notice is-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php esc_html_e( 'Email sent successfully via wp_mail().', 'admin-tools' ); ?>
        </div>
    <?php elseif ( isset( $_GET['smwm_error'] ) ) : ?>
        <?php $error = sanitize_key( $_GET['smwm_error'] ); ?>
        <div class="smwm-notice is-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php if ( 'missing_fields' === $error ) : ?>
                <?php esc_html_e( 'Please fill in all required fields — To, Subject, and Message.', 'admin-tools' ); ?>
            <?php else : ?>
                <?php esc_html_e( 'wp_mail() returned false. The email could not be sent — check your mail configuration.', 'admin-tools' ); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="smwm-alert">
        <span class="smwm-alert-icon">⚠</span>
        <span>
            <?php esc_html_e( 'For site admin use only — sends real emails via wp_mail() to verify your mail setup is working.', 'admin-tools' ); ?>
            <?php
            printf(
                esc_html__( 'Emails are sent from %1$s (%2$s).', 'admin-tools' ),
                '<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>',
                '<code>' . esc_html( get_option( 'admin_email' ) ) . '</code>'
            );
            ?>
        </span>
    </div>

    <div class="smwm-card">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="smwm_send_mail">
            <?php wp_nonce_field( 'smwm_send_mail_action', 'smwm_nonce' ); ?>

            <div class="smwm-field">
                <label class="smwm-label" for="smwm_to">
                    <?php esc_html_e( 'To', 'admin-tools' ); ?>
                    <span class="smwm-required" aria-hidden="true">*</span>
                </label>
                <input type="email" id="smwm_to" name="smwm_to" class="smwm-input"
                    value="<?php echo esc_attr( $_GET['smwm_to'] ?? '' ); ?>"
                    placeholder="recipient@example.com" required>
            </div>

            <div class="smwm-field">
                <label class="smwm-label" for="smwm_subject">
                    <?php esc_html_e( 'Subject', 'admin-tools' ); ?>
                    <span class="smwm-required" aria-hidden="true">*</span>
                </label>
                <input type="text" id="smwm_subject" name="smwm_subject" class="smwm-input"
                    value="<?php echo esc_attr( $_GET['smwm_subject'] ?? '' ); ?>"
                    placeholder="<?php esc_attr_e( 'Email subject', 'admin-tools' ); ?>" required>
            </div>

            <div class="smwm-field">
                <label class="smwm-label"><?php esc_html_e( 'Format', 'admin-tools' ); ?></label>
                <div class="smwm-format-group">
                    <label>
                        <input type="radio" name="smwm_format" value="plain" checked>
                        <span class="smwm-format-label"><?php esc_html_e( 'Plain Text', 'admin-tools' ); ?></span>
                    </label>
                    <label>
                        <input type="radio" name="smwm_format" value="html">
                        <span class="smwm-format-label"><?php esc_html_e( 'HTML', 'admin-tools' ); ?></span>
                    </label>
                </div>
            </div>

            <div class="smwm-field">
                <label class="smwm-label">
                    <?php esc_html_e( 'Message', 'admin-tools' ); ?>
                    <span class="smwm-required" aria-hidden="true">*</span>
                </label>
                <div id="smwm-plain-editor">
                    <textarea name="smwm_message_plain" rows="10"
                        placeholder="<?php esc_attr_e( 'Enter plain text message...', 'admin-tools' ); ?>"></textarea>
                </div>
                <div id="smwm-html-editor" style="display:none;">
                    <?php
                    wp_editor( '', 'smwm_message_html', array(
                        'media_buttons' => false,
                        'textarea_rows' => 12,
                        'teeny'         => false,
                    ) );
                    ?>
                </div>
            </div>

            <hr class="smwm-divider">

            <div class="smwm-footer">
                <p class="smwm-footer-meta">
                    <?php esc_html_e( 'From:', 'admin-tools' ); ?>
                    <span><?php echo esc_html( get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' ); ?></span>
                </p>
                <button type="submit" class="smwm-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <?php esc_html_e( 'Send Email', 'admin-tools' ); ?>
                </button>
            </div>
        </form>
    </div>

</div>
