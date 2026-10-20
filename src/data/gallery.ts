import imgHero from '../assets/images/hero_physics_classroom_1792301360099.jpg';
import imgTeacher from '../assets/images/teacher_mentorship_physics_1792301361137.jpg';
import imgDiscussion from '../assets/images/classroom_students_discussion_1792301360431.jpg';
import imgReading from '../assets/images/students_reading_concept_book_1792301361773.jpg';
import { GalleryItem } from '../types';

export const GALLERY_ITEMS: GalleryItem[] = [
  {
    id: 'gal-01',
    title: 'Photon Physics Academy Classroom',
    banglaCaption: 'ফোটন ফিজিক্স একাডেমির বাস্তব ক্লাসরুমে শিক্ষার্থীদের পাঠদান কার্যক্রম',
    imageUrl: imgHero,
    category: 'classroom',
  },
  {
    id: 'gal-02',
    title: 'Teacher-Led Physics Class',
    banglaCaption: 'শিক্ষকের পরিচালনায় শিক্ষার্থীদের পদার্থবিজ্ঞান ক্লাস',
    imageUrl: imgTeacher,
    category: 'mentorship',
  },
  {
    id: 'gal-03',
    title: 'Students in Class',
    banglaCaption: 'একাডেমির ক্লাসে অংশগ্রহণরত শিক্ষার্থীরা',
    imageUrl: imgDiscussion,
    category: 'study',
  },
  {
    id: 'gal-04',
    title: 'Concept Book Learning',
    banglaCaption: 'কনসেপ্ট বুক হাতে শিক্ষার্থীদের ক্লাসে অংশগ্রহণ',
    imageUrl: imgReading,
    category: 'study',
  },
];
