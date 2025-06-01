import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { v4 as uuidv4 } from 'uuid';

export interface ScheduledPost {
  id: string;
  feedId: string;
  feedName: string;
  title: string;
  content: string;
  siteId: string;
  siteName: string;
  scheduledDate: string;
  rewriteTone: string;
  status: 'scheduled' | 'paused' | 'published' | 'error';
  error?: string;
}

interface ScheduledPostsStore {
  posts: ScheduledPost[];
  addPost: (post: Omit<ScheduledPost, 'id' | 'status'>) => void;
  removePost: (id: string) => void;
  updatePost: (id: string, updates: Partial<ScheduledPost>) => void;
  togglePostStatus: (id: string) => void;
  reschedulePost: (id: string, newDate: string) => void;
}

export const useScheduledPostsStore = create<ScheduledPostsStore>()(
  persist(
    (set) => ({
      posts: [],
      addPost: (post) => set((state) => ({
        posts: [...state.posts, { ...post, id: uuidv4(), status: 'scheduled' }]
      })),
      removePost: (id) => set((state) => ({
        posts: state.posts.filter(post => post.id !== id)
      })),
      updatePost: (id, updates) => set((state) => ({
        posts: state.posts.map(post => 
          post.id === id ? { ...post, ...updates } : post
        )
      })),
      togglePostStatus: (id) => set((state) => ({
        posts: state.posts.map(post => 
          post.id === id ? {
            ...post,
            status: post.status === 'scheduled' ? 'paused' : 'scheduled'
          } : post
        )
      })),
      reschedulePost: (id, newDate) => set((state) => ({
        posts: state.posts.map(post =>
          post.id === id ? {
            ...post,
            scheduledDate: newDate,
            status: 'scheduled'
          } : post
        )
      }))
    }),
    {
      name: 'scheduled-posts'
    }
  )
);