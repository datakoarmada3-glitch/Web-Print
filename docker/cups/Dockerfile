FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y \
    cups \
    cups-client \
    cups-ipp-utils \
    printer-driver-gutenprint \
    --no-install-recommends \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure CUPS to allow remote access
RUN sed -i 's/Listen localhost:631/Listen 0.0.0.0:631/' /etc/cups/cupsd.conf && \
    sed -i '/<Location \/>/,/<\/Location>/s/Order allow,deny/Order allow,deny\n  Allow All/' /etc/cups/cupsd.conf && \
    sed -i '/<Location \/admin>/,/<\/Location>/s/Order allow,deny/Order allow,deny\n  Allow All/' /etc/cups/cupsd.conf && \
    sed -i '/<Location \/admin\/conf>/,/<\/Location>/s/Order allow,deny/Order allow,deny\n  Allow All/' /etc/cups/cupsd.conf

# Write a clean cupsd.conf that works
RUN cat > /etc/cups/cupsd.conf <<'EOF'
LogLevel warn
MaxLogSize 0
Listen 0.0.0.0:631
Listen /run/cups/cups.sock
Browsing Off
DefaultAuthType Basic
WebInterface Yes

<Location />
  Order allow,deny
  Allow All
</Location>

<Location /admin>
  Order allow,deny
  Allow All
</Location>

<Location /admin/conf>
  Order allow,deny
  Allow All
</Location>

<Policy default>
  JobPrivateAccess default
  JobPrivateValues default
  SubscriptionPrivateAccess default
  SubscriptionPrivateValues default

  <Limit Create-Job Print-Job Print-URI Validate-Job>
    Order deny,allow
    Allow All
  </Limit>

  <Limit Send-Document Send-URI Hold-Job Release-Job Restart-Job Purge-Jobs Set-Job-Attributes Create-Job-Subscription Renew-Subscription Cancel-Subscription Get-Notifications Reprocess-Job Cancel-Current-Job Suspend-Current-Job Resume-Job Cancel-My-Jobs Close-Job CUPS-Move-Job>
    Order deny,allow
    Allow All
  </Limit>

  <Limit All>
    Order deny,allow
    Allow All
  </Limit>
</Policy>
EOF

EXPOSE 631

CMD ["cupsd", "-f"]
