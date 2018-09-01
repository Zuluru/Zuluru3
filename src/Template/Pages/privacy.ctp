<?php
use Cake\Core\Configure;
?>
<div class="privacy">
<h2><?= __('Privacy Policy') ?></h2>
<h3><?= __('What information do we collect?') ?></h3>
<p><?= __('We collect information from you when you register on our site.') ?></p>
<p><?= __('When registering on our site, you may be asked to enter your: name, email address, mailing address or phone numbers. When registering, or at any later time, you may choose whether or not to publish your email address and phone numbers (collectively referred to as "contact information"); any such contact information that you choose to publish will be visible only to other registered members who are looged in.') ?></p>
<p><?= __('You may visit our site anonymously; however, to protect the privacy of others, some functionality is available only if you are registered and logged in.') ?></p>
<h3><?= __('What do we use your information for?') ?></h3>
<p><?= __('Any of the information we collect from you may be used in one of the following ways:') ?></p>
<ul>
<li><?= __('To personalize your experience (your information helps us to better respond to your individual needs)') ?></li>
<li><?= __('To process transactions') ?></li>
<li><?= __('To send periodic emails') ?></li>
</ul>
<p><?= __('The email address you provide may be used to send you information, respond to inquiries, and/or other requests or questions. Your information, whether public or private, will not be sold, exchanged, transferred, or given to any other company for any reason whatsoever, without your consent.') ?></p>
<h3><?= __('How do we protect your information?') ?></h3>
<p><?= __('All supplied credit information is transmitted via Secure Socket Layer (SSL) technology and then encrypted into our Payment gateway providers database only to be accessible by those authorized with special access rights to such systems, and are required to keep the information confidential.') ?></p>
<p><?= __('After a transaction, your credit card information will not be stored on our servers.') ?></p>
<h3><?= __('Do we disclose any information to outside parties?') ?></h3>
<p><?= __('We do not sell, trade, or otherwise transfer to outside parties your personally identifiable information. This does not include trusted third parties who assist us in operating our website, conducting our business, or servicing you, so long as those parties agree to keep this information confidential. We may also release your information when we believe release is appropriate to comply with the law, enforce our site policies, or protect ours or others rights, property, or safety. However, non-personally identifiable visitor information may be provided to other parties for marketing, advertising, or other uses.') ?></p>
<h3><?= __('Do we disclose any information to other members?') ?></h3>
<p><?= __('In order to facilitate team communication, the {0} software in use on this site may provide your contact information, even if not otherwise published, to certain individuals. In particular:', ZULURU) ?></p>
<ul>
<li><?= __('Any captain of a team may see the contact information for all of their players and for the coordinator and other captains of the division that the team is in') ?></li>
<li><?= __('Any player on a team may see the contact information for all of their captains') ?></li>
<li><?= __('Any coordinator of a division may see the contact information for all of the captains of all teams in their division') ?></li>
</ul>
<p><?= __('In the above, "captain" includes assistant captains and coaches, "player" includes anyone who has accepted an invitation to join the roster, and "team" and "division" refer only to those which are currently active (e.g. the captain of a team in a division which has completed can no longer access the contact information of players on that team).') ?></p>
<p><?= __('In order to facilitate team organization, the {0} software in use on this site may provide your {1}, even if not otherwise published, to certain individuals. In particular:', ZULURU, Configure::read('gender.name')) ?></p>
<ul>
<li><?= __('Any member of a team may see the {0} for all of the players on that team', Configure::read('gender.name')) ?></li>
<li><?= __('Any coordinator of a division may see the {0} for all players in their division', Configure::read('gender.name')) ?></li>
</ul>
<p><?= __('In the above, "member of a team" includes anyone (including captains, coaches, players and subs) who has accepted an invitation to join the roster, and "team" and "division" refer only to those which are currently active (e.g. members of a team in a division which has completed can no longer access the {0} of players on that team).', Configure::read('gender.name')) ?></p>
<h3><?= __('Your Consent') ?></h3>
<p><?= __('By using our site, you consent to this online privacy policy.') ?></p>
<h3><?= __('Changes to our Privacy Policy') ?></h3>
<p><?= __('If we decide to change our privacy policy, we will post those changes on this page.') ?></p>
</div>
