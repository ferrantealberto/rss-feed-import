import { Grid, Card, CardContent, Typography, Box } from '@mui/material';
import { RssFeed, Article, Image, Schedule } from '@mui/icons-material';

interface StatCardProps {
  title: string;
  value: number;
  icon: React.ReactNode;
  color: string;
}

function StatCard({ title, value, icon, color }: StatCardProps) {
  return (
    <Card>
      <CardContent>
        <Box display="flex" alignItems="center" gap={2}>
          <Box
            sx={{
              backgroundColor: `${color}15`,
              borderRadius: 2,
              p: 1,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}
          >
            {icon}
          </Box>
          <Box>
            <Typography variant="h4" component="div">
              {value}
            </Typography>
            <Typography color="textSecondary">{title}</Typography>
          </Box>
        </Box>
      </CardContent>
    </Card>
  );
}

export function Dashboard() {
  // Example stats - these would come from your actual data
  const stats = {
    activeFeeds: 5,
    postsImported: 128,
    imagesImported: 96,
    scheduledImports: 3,
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Dashboard
      </Typography>

      <Grid container spacing={3}>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Active Feeds"
            value={stats.activeFeeds}
            icon={<RssFeed sx={{ color: '#2271b1' }} />}
            color="#2271b1"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Posts Imported"
            value={stats.postsImported}
            icon={<Article sx={{ color: '#46b450' }} />}
            color="#46b450"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Images Imported"
            value={stats.imagesImported}
            icon={<Image sx={{ color: '#dba617' }} />}
            color="#dba617"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatCard
            title="Scheduled Imports"
            value={stats.scheduledImports}
            icon={<Schedule sx={{ color: '#dc3232' }} />}
            color="#dc3232"
          />
        </Grid>
      </Grid>

      <Box mt={4}>
        <Typography variant="h5" gutterBottom>
          Recent Activity
        </Typography>
        <Card>
          <CardContent>
            <Typography variant="body1" color="textSecondary">
              The activity feed will show recent imports, rewriting operations, and WordPress publishing events.
            </Typography>
          </CardContent>
        </Card>
      </Box>
    </Box>
  );
}