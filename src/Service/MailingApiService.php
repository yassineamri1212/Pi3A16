<?php
                                        // src/Service/MailingApiService.php

                                        namespace App\Service;

                                        use Symfony\Component\Mailer\MailerInterface;
                                        use Symfony\Component\Mime\Email;
                                        use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

                                        class MailingApiService
                                        {
                                            private MailerInterface $mailer;
                                            private UrlGeneratorInterface $urlGenerator;

                                            public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
                                            {
                                                $this->mailer = $mailer;
                                                $this->urlGenerator = $urlGenerator;
                                            }

                                            public function sendReponseNotification(string $recipientEmail, string $recipientUsername): void
                                            {
                                                $reclamationLink = $this->urlGenerator->generate('app_reclamation_my', [], UrlGeneratorInterface::ABSOLUTE_URL);
                                                $email = (new Email())
                                                    ->from('no-reply@demomailtrap.co')
                                                    ->to('ccsucks122@gmail.com')
                                                    ->subject('GoMobility Reclamation Response')
                                                    ->html(sprintf(
                                                        'Hello %s,<br/><br/>You got an answer in GoMobility. Go check it out <a href="%s">here</a>!',
                                                        $recipientUsername,
                                                        $reclamationLink
                                                    ));

                                                $this->mailer->send($email);
                                            }
                                        }