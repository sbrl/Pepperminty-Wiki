% From https://superuser.com/q/96970/164168
% TODO: Figure out how to induce lilypond to execute this *first*
\paper {
  indent = 0\mm
  line-width = 200\mm
  oddHeaderMarkup = ""
  evenHeaderMarkup = ""
  oddFooterMarkup = ""
  evenFooterMarkup = ""
}


% Created on Sun Feb 26 14:01:54 PST 2012
\version "2.14.0"

\header {
	title = "I'll Walk With You" 
 	composer = "Carol Lynn Pearson and Reid N Nibley" 
 	
 	%tagline = "169"
}
\paper {
 oddFooterMarkup = \markup { "Childrens Songbook 140-141" } 
 
}

\include "predefined-ukulele-fretboards.ly"
 
mynotes = {
	  
	s2. fis4
	d e fis g
	e e8 d e2
	b4 cis8  d e4 fis
	d d d fis
	<d a'>4 g2 <cis, a'>4
	<d fis>2. fis4
	d e fis g
	e e8 d e2
	b4 cis8 d e4 fis
	d d d fis
	<d a'>4 g2 <cis, a'>4
	<d fis>2. d4
	<d g> fis e e
	<d a'> g fis fis
	e fis g a
	<e b'>2 cis
	d1 (
	d2.) r4
	<fis d'>4 b <d, b'> cis'
	<cis, a'> a' <cis, a'> a'
	<e c'> a <c, a'> b'
	d, d d fis
	<d a'> g2 <cis, a'>4
	<d fis>1
	fis4 e fis e 
	e e a e 
	e d e d 
	<d g> a' b g
	<cis, fis>4 e2 fis4
	d1
	<d a'>4 g2 <cis, a'>4
	<d fis>2. d4
	<d g> fis e e 
	<d a'> g fis fis 
	e fis g a 
	<d, e b'>2 <cis cis'> 
	<d d'>1
	
	
}

myFretChords = {
	\chordmode {
	 d e:m g a:7 b:m e:7 a d:7 a:m
	
	}	
}
\score{
	<<
	\new ChordNames {
      \myFretChords
    }
    \new FretBoards {
      \set Staff.stringTunings = #ukulele-tuning
      \myFretChords
    }
>>
}


\score { 
	<<
	
		
		
	\new ChordNames {
		\chordmode { 
			d1 s  e:m
			g2 a:7 d1 g2 a:7 d1
			s e:m g2 a:7 d1
			g2 a:7 d1 e:m d
			e:m s2 a:7 d1
			s
			b2:m e:7 
			a1 a2:m d:7 g1 e2:m a:7 d1
			e:7 a d:7 g
			 a:7 d g2 a:7
			 d1 e:m
			 d1 e:m s2 a:7 d1
			
		}

	}
	
	\new Staff {
		
		\time 4/4		
		\clef treble
		%\transpose cis d {
		\key d \major
		\relative c' { 	
		 % Type notes here 
		  \mynotes			
		}	
		% }
	}
	
	
	
	\addlyrics {
		%verse 1
		  
		  If you don't walk as most peo -- ple do,
Some peo -- ple walk a -- way from you,
But I won't! I won't!
If you don't talk as most peo -- ple do,
Some peo -- ple talk and laugh at you,
But I won't! I won't!
I'll walk with you. I'll talk with you.
That's how I'll show my love for you.
Je -- sus walked a -- way from none.
He gave his love to ev -- 'ry -- one.
So I will! I will!
Je -- sus blessed all he could see,
Then turned and said, Come, fol -- low me.
And I will! I will!
I will! I will!
I'll walk with you. I'll talk with you.
That's how I'll show my love for you.
		    
		 
	}
	\addlyrics{
		%verse 2
		

	
	}
	
	
	\new TabStaff {
		\set TabStaff.stringTunings = #ukulele-tuning
		%\transpose cis d{
		\relative c'{
			\mynotes
			%\myNewNotes
		}
		% }
	}

>>
%\midi{}


}
	

	
